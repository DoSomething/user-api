@if (config('services.analytics.snowplow_url'))
    <script type='text/javascript'>
        (function(p,l,o,w,i,n,g){if(!p[i]){p.GlobalSnowplowNamespace=p.GlobalSnowplowNamespace||[];
          p.GlobalSnowplowNamespace.push(i);p[i]=function(){(p[i].q=p[i].q||[]).push(arguments)
          };p[i].q=p[i].q||[];n=l.createElement(o);g=l.getElementsByTagName(o)[0];n.async=1;
          n.src=w;g.parentNode.insertBefore(n,g)}}(window,document,'script','//d1fc8wv8zag5ca.cloudfront.net/2.5.3/sp.js','snowplow'));
          window.snowplow('newTracker', 'cf', '{{config('services.analytics.snowplow_url')}}', {
            appId: 'northstar',
            cookieDomain: null,
            discoverRootDomain: true
        });

        window.snowplow('trackPageView');
    </script>
@endif
