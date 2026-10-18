import sys,glob
from PIL import Image
tag=sys.argv[1]; sc=float(sys.argv[2]) if len(sys.argv)>2 else 0.5
i=0
while glob.glob(f'ph/pr-{tag}-{i}-*.png'):
    ims=[Image.open(f) for f in sorted(glob.glob(f'ph/pr-{tag}-{i}-*.png'))]  # p then s
    w=sum(int(x.width*sc) for x in ims)+12; h=max(int(x.height*sc) for x in ims)
    S=Image.new('RGB',(w,h),'white'); x0=0
    for im in ims: S.paste(im.resize((int(im.width*sc),int(im.height*sc))),(x0,0)); x0+=int(im.width*sc)+12
    S.save(f'ph/PR-{tag}-{i}.png'); print(i,S.size); i+=1
