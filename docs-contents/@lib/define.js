function base64urlToBuffer(base64url) {
    const padding = '='.repeat((4 - (base64url.length % 4)) % 4);
    const base64 = (base64url + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray.buffer;
}
function get_GETarray() {
    let q=(decodeURI(location.search)+'&').replace(/^\?/,'').split('&');
    let queries=[];
    for (let i=0; i<q.length; i++) {
        if(q[i].length>0){
            if(q[i].indexOf('=')>0){
                query=q[i].split('=');
                queries[query[0]]=query[1];
            } else {
                queries[q[i]]=null;
            }
        }
    }
    return queries;
}
function stringToBinary(str) {
    const encoder = new TextEncoder();
    const bytes = encoder.encode(str);
    return Array.from(bytes)
        .map(byte => byte.toString(2).padStart(8, '0'))
        .join('');
}
