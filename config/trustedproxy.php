<?php

// Pre-process any comma-separated values into an array.
$trustedProxies = env('TRUSTED_PROXY_IP_ADDRESSES');
if (str_contains($trustedProxies, ',')) {
    $trustedProxies = explode(',', $trustedProxies);
}

return [
    /*
     * Set trusted proxy IP addresses.
     *
     * Both IPv4 and IPv6 addresses are supported, along with CIDR notation.
     *
     * The "*" character is syntactic sugar within TrustedProxy to trust any proxy
     * that connects directly to your server,a requirement when you cannot know the
     * address of your proxy (e.g. if using Rackspace balancers).
     *
     * The "**" character is syntactic sugar within TrustedProxy to trust not just any
     * proxy that connects directly to your server, but also proxies that connect to
     * those proxies, and all the way back until you reach the original source IP.
     *
     * It will mean that $request->getClientIp() always gets the originating client IP,
     * no matter how many proxies that client's request has subsequently passed through.
     */
    'proxies' => $trustedProxies,

    /*
     * Default Header Names
     *
     * Change these if the proxy does not send the default header names.
     */
    'headers' => [
        \Illuminate\Http\Request::HEADER_FORWARDED => null, // Not set on AWS or Heroku.
        \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR => 'X_FORWARDED_FOR',
        \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST => null, // Not set on AWS or Heroku.
        \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT => 'X_FORWARDED_PORT',
        \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO => 'X_FORWARDED_PROTO',
    ],
];
