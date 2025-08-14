const CACHE='ntp-1';const ASSETS=['/','/css/style.css?v=2','/images/logo.png','/images/preview.jpg'];
self.addEventListener('install',e=>{e.waitUntil(caches.open(CACHE).then(c=>c.addAll(ASSETS)))});
self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(keys=>Promise.all(keys.map(k=>k!==CACHE&&caches.delete(k))))) });
self.addEventListener('fetch',e=>{const req=e.request;
  e.respondWith(caches.match(req).then(res=>res||fetch(req).then(net=>{
    if(req.method==='GET' && net && net.status===200){
      const copy=net.clone(); caches.open(CACHE).then(c=>c.put(req,copy)).catch(()=>{});
    }
    return net;
  }).catch(()=>res)));
});