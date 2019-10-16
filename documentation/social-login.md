# Social Login

Northstar uses [Laravel Socialite](https://laravel.com/docs/5.5/socialite) to support login/registration via Facebook or Google.

To test social login on your local Northstar instance, [SSH into your Homestead machine via `vagrant ssh` and run `share northstar.test` to make your local Northstar instance available to the public](https://laravel.com/docs/5.5/homestead#sharing-your-environment).

Once this is running, your local Northstar instance will be available at a URL like `http://f782f37a.ngrok.io`. We'll need to add this URL to the OAuth configuration in each platform (Facebook, Google) we wish to test against.

## Google

We have three Google Cloud projects set up for each Northstar environment: Northstar-Dev, Northstar-QA, and Northstar-Prod. 

Each Google Cloud project is set up with an OAuth consent screen, which is [configured with an additional scope to read a user's birthday](https://user-images.githubusercontent.com/1236811/66961342-f4ba8500-f05d-11e9-80d9-ff089d052728.png). 

Each Google Cloud project also has a Web Application OAuth client set up, which provides the values to use for the `GOOGLE_CLIENT_ID` AND `GOOGLE_CLIENT_SECRET` config vars.

Each Google Cloud project also has the Google People API enabled, which we use to fetch the user's birthday upon authentication.

### Development

For local development, use the Northstar-Dev project.

* Add your ngrok URL, e.g. `http://f782f37a.ngrok.io`, to the [list of authorized domains in the OAuth consent screen](https://user-images.githubusercontent.com/1236811/66961310-e5d3d280-f05d-11e9-8b63-b7f2c0a3218d.png).

* Once that has been saved, add `http://f782f37a.ngrok.io/google/verify` to the list of authorized redirect URI's in the [OAuth web client](https://user-images.githubusercontent.com/1236811/66961589-8c1fd800-f05e-11e9-859d-fbc9059521d7.png)

* Update your local `GOOGLE_REDIRECT_URL` config var to `http://f782f37a.ngrok.io/google/verify`

* Visiting `http://f782f37a.ngrok.io/google/continue` should now direct you to the Google OAuth consent screen. Allowing access should result in redirecting back to `http://f782f37a.ngrok.io` as your authenticated Google user.

## Facebook

TODO: Add info here
