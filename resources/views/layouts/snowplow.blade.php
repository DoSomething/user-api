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
    </script>
@else
    {{-- Custom script for logging Snowplow events to the console when no ENV variable provided. --}}
    <script type='text/javascript'>
        window.snowplow = function () {
            console.groupCollapsed(
                '%c SNOWPLOW: %c %c %s %c@%c %s %c',
                'background-color: rgba(96,47,175,0.5); color: rgba(97,68,144,1); display: inline-block; font-weight: bold; line-height: 1.5;',
                'background-color: transparent; color: rgba(165, 162, 162, 1); font-weight: normal; letter-spacing: 3px; line-height: 1.5;',
                'color: black; font-weight: bold; letter-spacing: normal; line-height: 1.5;',
                'Function Name',
                'color: rgba(165, 162, 162, 0.8); font-weight: normal;',
                'color: black; font-weight: bold;',
                arguments[0],
                'background-color: rgba(105,157,215,0.5);',
            );

            switch (arguments[0]) {
                case 'setUserId':
                    console.log('User ID:', arguments[1]);
                    break;

                case 'trackStructEvent':
                    console.log('Category: ', arguments[1]);
                    console.log('Action: ', arguments[2]);
                    console.log('Label: ', arguments[3]);
                    console.log('Name: ', arguments[4]);
                    // arguments[5] is always null
                    console.log('Context: ', JSON.parse(arguments[6][0]['data']['payload']));
                    break;

                case 'trackPageView':
                    console.log('Context: ', JSON.parse(arguments[2][0]['data']['payload']));
                    break;

                default:
                    console.log(arguments);
            }
            console.groupEnd();
        };
    </script>
@endif
