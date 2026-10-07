// For isolated ORIGINAL client builds only, never for dependency restoration.
// NODE_OPTIONS propagates this guard to Node compiler/render workers.
const net = require('node:net');
const tls = require('node:tls');
const http = require('node:http');
const https = require('node:https');
const dns = require('node:dns');
function denied() {
  throw new Error('Original baseline blocks network operations during client builds.');
}
// Compilation workers can retain Unix-domain IPC, but no TCP connections.
const connect = net.Socket.prototype.connect;
net.Socket.prototype.connect = function (...args) {
  const first = args[0];
  if (first && typeof first === 'object' && typeof first.path === 'string') {
    return connect.apply(this, args);
  }
  return denied();
};
tls.connect = denied;
http.request = http.get = https.request = https.get = denied;
dns.lookup = dns.resolve = denied;
dns.promises.lookup = dns.promises.resolve = denied;
globalThis.fetch = denied;