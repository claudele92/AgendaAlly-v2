'use strict';

// Loaded with NODE_OPTIONS only for the restored original client dev servers.
// The only non-loopback service they may call is this workspace's isolated API.
const dns = require('node:dns');
const http = require('node:http');
const http2 = require('node:http2');
const net = require('node:net');
const tls = require('node:tls');

const rawApiHost = process.env.REPLIT_DEV_DOMAIN;
const apiHost = typeof rawApiHost === 'string' ? rawApiHost.toLowerCase() : '';
const apiPort = 8000;
const apiAddresses = new Set();

function fail(reason) {
  const error = new Error(`Original preview network guard blocked ${reason}.`);
  error.code = 'ERR_ORIGINAL_PREVIEW_NETWORK_BLOCKED';
  throw error;
}

function validDnsName(host) {
  if (!host || host.length > 253 || host.endsWith('.')) return false;
  return host.split('.').every((label) =>
    label.length > 0 &&
    label.length <= 63 &&
    /^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/.test(label),
  );
}

if (!validDnsName(apiHost) || net.isIP(apiHost)) {
  fail('startup: REPLIT_DEV_DOMAIN is not a plain public DNS hostname');
}

function normalizedHost(host) {
  if (typeof host !== 'string') return '';
  let value = host.toLowerCase().replace(/^\[|\]$/g, '');
  if (value.endsWith('.')) value = value.slice(0, -1);
  return value;
}

function isLoopbackHost(host) {
  const value = normalizedHost(host);
  const address = value.split('%')[0];
  return value === 'localhost' ||
    (net.isIP(address) !== 0 && net.isIPv6(address) &&
      address.toLowerCase() === '::1') ||
    (net.isIPv4(address) && address.split('.').map(Number)[0] === 127);
}

function rememberAddresses(answer) {
  const values = Array.isArray(answer) ? answer : [answer];
  for (const value of values) {
    const address = typeof value === 'string' ? value : value?.address;
    if (typeof address === 'string' && net.isIP(address) !== 0) {
      apiAddresses.add(address.toLowerCase());
    }
  }
}

function assertDnsAllowed(host) {
  const value = normalizedHost(host);
  if (value !== apiHost && value !== '0.0.0.0' && !isLoopbackHost(value)) {
    fail(`DNS lookup for ${String(host)}`);
  }
}

function wrapDnsMethod(target, method, remember) {
  const original = target?.[method];
  if (typeof original !== 'function') return;
  target[method] = function guardedDnsMethod(host, ...args) {
    assertDnsAllowed(host);
    if (!remember) return original.call(this, host, ...args);
    const callbackIndex = args.findLastIndex((arg) => typeof arg === 'function');
    if (callbackIndex >= 0) {
      const callback = args[callbackIndex];
      args[callbackIndex] = function rememberDnsResult(error, result, ...rest) {
        if (!error && normalizedHost(host) === apiHost) rememberAddresses(result);
        return callback.call(this, error, result, ...rest);
      };
      return original.call(this, host, ...args);
    }
    return Promise.resolve(original.call(this, host, ...args)).then((result) => {
      if (normalizedHost(host) === apiHost) rememberAddresses(result);
      return result;
    });
  };
}

wrapDnsMethod(dns, 'lookup', true);
for (const method of [
  'resolve', 'resolve4', 'resolve6', 'resolveAny', 'resolveCaa',
  'resolveCname', 'resolveMx', 'resolveNaptr', 'resolveNs', 'resolvePtr',
  'resolveSoa', 'resolveSrv', 'resolveTlsa', 'resolveTxt', 'resolveSvcb',
  'resolveHttps', 'reverse',
]) {
  wrapDnsMethod(dns, method, true);
}
const dnsPromises = dns.promises;
wrapDnsMethod(dnsPromises, 'lookup', true);
for (const method of [
  'resolve', 'resolve4', 'resolve6', 'resolveAny', 'resolveCaa',
  'resolveCname', 'resolveMx', 'resolveNaptr', 'resolveNs', 'resolvePtr',
  'resolveSoa', 'resolveSrv', 'resolveTlsa', 'resolveTxt', 'resolveSvcb',
  'resolveHttps', 'reverse',
]) {
  wrapDnsMethod(dnsPromises, method, true);
}
for (const Resolver of [dns.Resolver, dnsPromises.Resolver]) {
  if (!Resolver?.prototype) continue;
  if (typeof Resolver.prototype.setServers === 'function') {
    Resolver.prototype.setServers = function blockedDnsServerOverride() {
      fail('a custom DNS resolver');
    };
  }
  for (const method of [
    'resolve', 'resolve4', 'resolve6', 'resolveAny', 'resolveCaa',
    'resolveCname', 'resolveMx', 'resolveNaptr', 'resolveNs', 'resolvePtr',
    'resolveSoa', 'resolveSrv', 'resolveTlsa', 'resolveTxt', 'resolveSvcb',
    'resolveHttps', 'reverse',
  ]) {
    wrapDnsMethod(Resolver.prototype, method, true);
  }
}
for (const target of [dns, dnsPromises]) {
  const originalLookupService = target.lookupService;
  if (typeof originalLookupService !== 'function') continue;
  target.lookupService = function guardedLookupService(address, port, ...args) {
    const host = normalizedHost(address);
    if (host !== '0.0.0.0' && !isLoopbackHost(host) && !apiAddresses.has(host)) {
      fail(`reverse DNS lookup for ${String(address)}`);
    }
    return originalLookupService.call(this, address, port, ...args);
  };
}
if (typeof dns.setServers === 'function') {
  dns.setServers = function blockedDefaultDnsServerOverride() {
    fail('a custom DNS resolver');
  };
}

function assertTcpAllowed(host, port) {
  const value = normalizedHost(host || 'localhost');
  const numericPort = Number(port || 0);
  if (value === '0.0.0.0') {
    fail(`TCP connection to ${value}:${numericPort || 'default'}`);
  }
  if (isLoopbackHost(value)) return;
  if (value === apiHost && numericPort === apiPort) return;
  if (apiAddresses.has(value) && numericPort === apiPort) return;
  fail(`TCP connection to ${value || String(host)}:${numericPort || 'default'}`);
}

function socketTarget(args) {
  const first = args[0];
  if (typeof first === 'string') {
    // net.Socket.connect(string) is the Unix-domain/Windows IPC overload.
    return { ipc: true };
  }
  if (first && typeof first === 'object') {
    if (typeof first.path === 'string') return { ipc: true };
    return {
      host: first.host || first.hostname || 'localhost',
      port: first.port,
    };
  }
  if (typeof first === 'number') {
    return {
      host: typeof args[1] === 'string' ? args[1] : 'localhost',
      port: first,
    };
  }
  fail('an unrecognized TCP socket target');
}

const originalSocketConnect = net.Socket.prototype.connect;
net.Socket.prototype.connect = function guardedSocketConnect(...args) {
  const target = socketTarget(args);
  if (!target.ipc) assertTcpAllowed(target.host, target.port);
  return originalSocketConnect.apply(this, args);
};
const originalNetConnect = net.connect;
function guardedNetConnect(...args) {
  const target = socketTarget(args);
  if (!target.ipc) assertTcpAllowed(target.host, target.port);
  return originalNetConnect.apply(this, args);
}
net.connect = guardedNetConnect;
net.createConnection = guardedNetConnect;

function targetFromRequest(input, options, protocol) {
  let url;
  if (input instanceof URL) {
    url = new URL(input.href);
  } else if (typeof input === 'string') {
    try {
      url = new URL(input);
    } catch {
      fail(`an unparseable ${protocol} request target`);
    }
  }

  const requestOptions = options && typeof options === 'object'
    ? options
    : input && typeof input === 'object' && !(input instanceof URL)
      ? input
      : {};
  if (requestOptions.socketPath) return { ipc: true };
  const scheme = requestOptions.protocol || url?.protocol || protocol;
  const host = requestOptions.hostname || requestOptions.host || url?.hostname || 'localhost';
  const port = requestOptions.port || url?.port ||
    (String(scheme).replace(/:$/, '') === 'https' ? 443 : 80);
  return { host, port, protocol: String(scheme).replace(/:$/, '').toLowerCase() };
}

function assertRequestAllowed(target, scheme) {
  if (target.ipc) return;
  const protocol = (target.protocol || scheme).replace(/:$/, '').toLowerCase();
  const host = normalizedHost(target.host);
  const port = Number(target.port);
  if (isLoopbackHost(host)) return;
  if (protocol === 'https' && host === apiHost && port === apiPort) return;
  fail(`${protocol.toUpperCase()} request to ${host}:${port}`);
}

function wrapRequest(module, method, defaultProtocol) {
  const original = module[method];
  module[method] = function guardedRequest(input, options, ...rest) {
    const hasUrlOptions = input instanceof URL || typeof input === 'string';
    const target = targetFromRequest(
      input,
      hasUrlOptions ? options : undefined,
      defaultProtocol,
    );
    assertRequestAllowed(target, defaultProtocol);
    return original.call(this, input, options, ...rest);
  };
}

for (const method of ['request', 'get']) {
  wrapRequest(http, method, 'http');
  wrapRequest(require('node:https'), method, 'https');
}

const originalTlsConnect = tls.connect;
tls.connect = function guardedTlsConnect(...args) {
  const first = args[0];
  if (first instanceof net.Socket) {
    if (first.remoteAddress) {
      assertTcpAllowed(first.remoteAddress, first.remotePort);
    } else {
      fail('TLS over a socket with an unverified destination');
    }
    return originalTlsConnect.apply(this, args);
  }
  if (first && typeof first === 'object' && first.socket) {
    const socket = first.socket;
    if (socket.remoteAddress) {
      assertTcpAllowed(socket.remoteAddress, socket.remotePort);
    } else {
      fail('TLS over a socket with an unverified destination');
    }
  } else {
    const target = socketTarget(args);
    if (!target.ipc) assertTcpAllowed(target.host, target.port || 443);
  }
  return originalTlsConnect.apply(this, args);
};

const originalHttp2Connect = http2.connect;
http2.connect = function guardedHttp2Connect(authority, ...args) {
  let url;
  try {
    url = new URL(authority);
  } catch {
    fail('an unparseable HTTP/2 request target');
  }
  assertRequestAllowed({
    protocol: url.protocol,
    host: url.hostname,
    port: url.port || (url.protocol === 'https:' ? 443 : 80),
  }, url.protocol);
  return originalHttp2Connect.call(this, authority, ...args);
};

if (typeof globalThis.fetch === 'function') {
  const originalFetch = globalThis.fetch;
  globalThis.fetch = function guardedFetch(input, init = {}) {
    const rawUrl = input instanceof URL ? input.href : input?.url || input;
    let url;
    try {
      url = new URL(rawUrl);
    } catch {
      fail('an unparseable fetch request target');
    }
    if (url.username || url.password) fail('credentials embedded in a fetch URL');
    assertRequestAllowed({
      protocol: url.protocol,
      host: url.hostname,
      port: url.port || (url.protocol === 'https:' ? 443 : 80),
    }, url.protocol);
    // Never follow redirects: an allowed API response must not become a route
    // for reaching a third-party provider.
    return originalFetch.call(this, input, { ...init, redirect: 'manual' });
  };
}

// Permit UDP only when its connected/send destination is loopback. Next's
// dev workers use TCP or IPC; this also prevents datagram-based egress.
const dgram = require('node:dgram');
const originalCreateSocket = dgram.createSocket;
dgram.createSocket = function guardedCreateSocket(...args) {
  const socket = originalCreateSocket.apply(this, args);
  const originalSend = socket.send;
  socket.send = function guardedDatagramSend(...sendArgs) {
    const destination = sendArgs.findLast((arg) => typeof arg === 'string');
    if (!destination || !isLoopbackHost(destination)) {
      fail(`UDP datagram to ${String(destination || 'an unknown destination')}`);
    }
    return originalSend.apply(this, sendArgs);
  };
  const originalConnect = socket.connect;
  socket.connect = function guardedDatagramConnect(port, host, ...rest) {
    if (!isLoopbackHost(host)) fail(`UDP connection to ${String(host)}:${port}`);
    return originalConnect.call(this, port, host, ...rest);
  };
  return socket;
};