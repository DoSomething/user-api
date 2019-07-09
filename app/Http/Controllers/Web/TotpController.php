<?php

namespace Northstar\Http\Controllers\Web;

use OTPHP\TOTP;
use OTPHP\Factory;
use Endroid\QrCode\QrCode;
use Northstar\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Northstar\Http\Controllers\Controller;

class TotpController extends Controller
{
    /**
     * Make a new TotpController, inject dependencies,
     * and set middleware for this controller's methods.
     */
    public function __construct()
    {
        $this->middleware('auth:web', ['only' => ['create', 'store']]);
    }

    /**
     * Display the TOTP prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function prompt()
    {
        return view('totp.prompt');
    }

    /**
     * Verify a given TOTP code & user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|numeric',
        ]);

        // The 2FA verification flow is initiated by redirecting a successfully
        // authenticated user from AuthController@postLogin. The to-be-authenticated
        // user ID is stored in the backend session:
        $id = session()->pull('totp.user');

        // If we aren't fulfilling a login prompt, then no code will be valid:
        if (! $id) {
            return redirect('/login')->with('status', 'That wasn\'t a valid two-factor code. Try again!');
        }

        // Verify provided TOTP code for the user:
        $user = User::find($id);
        $totp = Factory::loadFromProvisioningUri($user->totp);
        if (! $totp->verify($request->code)) {
            return redirect('/login')->with('status', 'That wasn\'t a valid two-factor code. Try again!');
        }

        // Authenticate the user & redirect to intended location:
        Auth::login($user, true);

        return redirect()->intended('/');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function configure()
    {
        $user = Auth::user();

        // Don't overwrite an existing code.
        if ($user->totp) {
            return redirect('/')->with('status', 'You\'ve already configured a two-factor device.');
        }

        // Create a TOTP provisioning URI:
        $totp = TOTP::create();

        // We'll include some metadata so apps like Google Authenticator
        // can properly label the code for the user:
        $totp->setIssuer(config('app.name'));
        $totp->setLabel($user->email ?: $user->mobile);

        $uri = $totp->getProvisioningUri();
        $qr = new QrCode($uri);

        return view('totp.configure', ['user' => $user, 'uri' => $uri, 'qr' => $qr]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $this->validate($request, [
            'uri' => 'required',
            'code' => 'required|integer',
        ]);

        // Don't overwrite an existing code.
        if ($user->totp) {
            return redirect('/')->with('status', 'You\'ve already configured a two-factor device.');
        }

        // Verify the provided code & provisoning URI:
        $otp = Factory::loadFromProvisioningUri($request->uri);
        if (! $otp->verify($request->code)) {
            return back()->with('status', 'That code isn\'t valid. Try again!');
        }

        // Store the TOTP provisioning URI on the user now that
        // we know they've set it up on their authenticator.
        $user->totp = $request->uri;
        $user->save();

        return redirect('/')->with('status', 'You now have two-factor authentication enabled!');
    }
}
