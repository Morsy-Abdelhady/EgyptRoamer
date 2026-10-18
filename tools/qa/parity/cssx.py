import re,sys
html=open('v4-nodata.html',encoding='utf-8').read()
css="\n".join(re.findall(r'<style[^>]*>(.*?)</style>',html,re.S))
css=re.sub(r'/\*.*?\*/','',css,flags=re.S)
toks=sys.argv[1].split(',')
def blocks(s):
    out=[];i=0;n=len(s)
    while i<n:
        j=s.find('{',i)
        if j<0:break
        sel=s[i:j].strip();d=1;k=j+1
        while d and k<n:
            if s[k]=='{':d+=1
            elif s[k]=='}':d-=1
            k+=1
        out.append((sel,s[j+1:k-1]));i=k
    return out
hit=lambda sel:any(re.search(r'\.'+re.escape(t)+r'(?![\w-])',sel) or re.search(r'\.'+re.escape(t)+r'(__|--)',sel) for t in toks)
res=[]
for sel,body in blocks(css):
    if sel.startswith('@media') or sel.startswith('@container') or sel.startswith('@supports'):
        inner=[f"{a}{{{b}}}" for a,b in blocks(body) if hit(a)]
        if inner: res.append(sel+"{"+"".join(inner)+"}")
    elif sel.startswith('@keyframes'):
        if any(t in sel for t in toks): res.append(sel+"{"+body+"}")
    elif hit(sel): res.append(sel+"{"+body.strip()+"}")
print(len(css),len(res),file=sys.stderr)
open(sys.argv[2],'w',encoding='utf-8',newline='\n').write("\n".join(res))
