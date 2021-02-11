<dt>SMS Status:</dt><dd>{{ $user->sms_status ?? '—' }}</dd>
<dt>SMS Paused:</dt><dd>{{ $user->sms_paused ? '✔' : '✘' }}</dd>
<dt>SMS Subscription Topics:</dt><dd>{{ $user->sms_subscription_topics ? implode(",  ",$user->sms_subscription_topics) : '—'}}</dd>
<dt>Email Subscription Status:</dt><dd>{{ $user->email_subscription_status ? '✔' : '✘' }}</dd>
<dt>Email Subscription Topics:</dt><dd>{{ $user->email_subscription_topics ? implode(",  ",$user->email_subscription_topics) : '—'}}</dd>
<dt>Promotions Muted At:</dt><dd>{{ $user->promotions_muted_at ? $user->promotions_muted_at->format('F d, Y g:ia') : '—'}}</dd>
