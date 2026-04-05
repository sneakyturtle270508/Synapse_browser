import os
import httpx
from fastapi import FastAPI, Request
from fastapi.responses import HTMLResponse, JSONResponse
from fastapi.staticfiles import StaticFiles
import json

app = FastAPI()

SEARX_URL = os.getenv("SEARX_URL", "https://searx.work")

@app.get("/", response_class=HTMLResponse)
async def root():
    with open("index.html", "r") as f:
        return f.read()

@app.get("/api.php")
async def api_proxy(q: str):
    try:
        async with httpx.AsyncClient(timeout=30.0) as client:
            response = await client.get(
                f"{SEARX_URL}/search",
                params={
                    "q": q,
                    "format": "json",
                    "engines": "google,bing,duckduckgo"
                }
            )
            
            if response.status_code == 200:
                data = response.json()
                results = []
                for r in data.get("results", [])[:20]:
                    results.append({
                        "title": r.get("title", ""),
                        "url": r.get("url", ""),
                        "snippet": r.get("content", r.get("description", ""))
                    })
                
                github_profile = await fetch_github_profile(q)
                wikipedia_info = find_wikipedia_info(results)
                
                return JSONResponse({
                    "query": q,
                    "results": results,
                    "total": len(results),
                    "githubProfile": github_profile,
                    "wikipediaInfo": wikipedia_info,
                    "isPersonSearch": False
                })
            else:
                return JSONResponse({"error": "Search failed"}, status_code=500)
    except Exception as e:
        return JSONResponse({"error": str(e)}, status_code=500)

async def fetch_github_profile(query: str):
    words = query.strip().split()
    
    # Check if it looks like a name or username
    is_name = len(words) >= 2 and all(w[0].isupper() for w in words if w.isalpha())
    is_username = len(words) == 1 and words[0].isalnum()
    
    if not (is_name or is_username):
        return None
    
    # Generate possible usernames
    usernames = []
    if len(words) >= 2:
        first, last = words[0].lower(), words[-1].lower()
        usernames.extend([first + last, first + "-" + last, first + "_" + last, first])
    else:
        usernames.append(words[0].lower())
    
    async with httpx.AsyncClient(timeout=10.0) as client:
        for username in usernames:
            try:
                response = await client.get(
                    f"https://api.github.com/users/{username}",
                    headers={"Accept": "application/vnd.github.v3+json"}
                )
                if response.status_code == 200:
                    data = response.json()
                    return {
                        "username": data.get("login", ""),
                        "name": data.get("name", data.get("login", "")),
                        "bio": data.get("bio", ""),
                        "avatar": data.get("avatar_url", ""),
                        "url": data.get("html_url", ""),
                        "followers": data.get("followers", 0),
                        "following": data.get("following", 0),
                        "publicRepos": data.get("public_repos", 0)
                    }
            except:
                pass
    return None

def find_wikipedia_info(results):
    for r in results:
        if "wikipedia.org" in r.get("url", ""):
            return {
                "name": r.get("title", ""),
                "description": r.get("snippet", ""),
                "url": r.get("url", "")
            }
    return None

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=7860)
