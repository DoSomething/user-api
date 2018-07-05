# Discovery Endpoints

## Get OpenID Configuration
Get configuration details for Northstar's [OpenID Connect](http://openid.net/connect/) implementation.
This can be used to automatically configure API clients & resource servers.

```
GET /.well-known/openid-configuration
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X GET https://profile.dosomething.org/.well-known/openid-configuration -H "Accept: application/json"
```
</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
  "issuer": "https:\/\/profile.dosomething.org",
  "authorization_endpoint": "https:\/\/profile.dosomething.org\/authorize",
  "token_endpoint": "https:\/\/profile.dosomething.org\/v2\/auth\/token",
  "userinfo_endpoint": "https:\/\/profile.dosomething.org\/v2\/auth\/info",
  "jwks_uri": "https:\/\/profile.dosomething.org\/v2\/keys",
  "response_types_supported": [
    "code"
  ],
  "subject_types_supported": [
    "public"
  ],
  "id_token_signing_alg_values_supported": [
    "RS256"
  ]
}
```
</details>


## Retrieve Public Key
Retrieves the public key(s) which can be used to verify access tokens. These are returned
as a set of [JSON Web Keys](https://tools.ietf.org/html/draft-ietf-jose-json-web-key).

```
GET /v2/keys
```

<details>
<summary><strong>Example Request</strong></summary>

```sh
curl -X GET https://profile.dosomething.org/v2/keys -H "Accept: application/json"
```
</details>

<details>
<summary><strong>Example Response</strong></summary>

```js
// 200 OK

{
  "keys": [
    {
      "kty": "RSA",
      "e": "AQAB",
      "n": "yPqE7yOhOH5OaB4F8sVen-tfsMkI3eqz7z-HHCNsqHVbLl3gDkzoCxvedjOn-RbnTBT3LP_X7IbFxCPzoh3fl82EVyROu5BmO3-pToaTNAnd-HQEflS3HMoCcaB6tNbt0lY5VMq6WvhLQvofBxs-n4YhUotWiUflI60nzKZHAJLNzG3bEoBMBnvIijbItpG7WCrqanaMG6YyoueEsVeyL8tn5ZG8Opy8tYUKTebhaJi2Wv0vJXngUO2ubrrI6dNL3h6ZA4g7FhRZWhaiKIEIdJXATk6cPiog-TYy-sCB-TPeMV7EE1Sz0d03373FBIaCwo4raNMJvxDS2CAtpJMQWPBa6PGbLT4SdFaDJfd6nJMiVBhd6iESZzudGfYyTWsulUkNIroppeFqKdE1s5gsL86sG125PKVii5dHmds2Zpn6rBoqo-byb3vEsaib00z6LeUpQGH7vTdi79hwVrnKtRoAjg7dVnZwJdcRLlL6OoSUdUY-8yuM5lGcf2vx3VraP8btQUWSIFDCiqqeZz2fWw5ABqx3PCeYj4py1gpjji43MbU0-3jao4YAoSDz9llhF8KqYqm0X8jFBgkVpXNV-8D4uFeDhgf01IdZVsiwTzJW4aiOt908Ix4m9290Npp9C1IEaZDI4cdmHHdYCs1nkMT4tHwSEIJLQOKy7_3LI18",
      "kid": "5lTRtOLElOERDVPisOYtVcSgE3oLk-rOkkMOoE4rB6I"
    }
  ]
}
```
</details>

