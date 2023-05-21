<?php

namespace App\Http\Controllers\Auth;
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Models\User;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone_number' => ['required', 'numeric', 'unique:users'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'password' => Hash::make($data['password'] ?? ''),
            'phone_number' => $data['phone_number'] ?? '',
        ]);
    }
    
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $name = $request->name;
        $email = $request->email;
        $password = Hash::make($request->password);
        $phone_number = $request->phone_number;
        $otp = mt_rand(100000, 999999); // Generate random OTP

        // Store OTP in session
        $request->session()->put('name', $name);
        $request->session()->put('email', $email);
        $request->session()->put('password', $password);
        $request->session()->put('phone_number', $phone_number);
        $request->session()->put('otp', $otp);

        // Send OTP to user's WhatsApp using Twilio
        $this->sendOtpToWhatsApp($request->phone_number, $otp, $name);

        return redirect()->route('verify-otp');
    }

    public function showOtpVerificationForm(Request $request)
    {
        return view('auth.verify-otp');
    }
    public function verifyOtp(Request $request)
    {
        $name = $request->session()->get('name');
        $email = $request->session()->get('email');
        $password = $request->session()->get('password');
        $phone_number = $request->session()->get('phone_number');
        $otp = $request->session()->get('otp');

        if ($request->otp == $otp) {
            // OTP verification successful, proceed with user registration
            $user = $this->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'phone_number' => $phone_number,
            'otp' => $request->otp,
        ]);

            $this->guard()->login($user);

            return redirect($this->redirectPath());
        } else {
            // Invalid OTP
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }
    }

    private function sendOtpToWhatsApp($phoneNumber, $otp, $name)
    {
        $sid = "isi sid anda";
        $token = "isi token anda";
        $twilioPhoneNumber = "isi no twilio anda";

        $client = new Client($sid, $token);

        $message = $client->messages->create(
            "whatsapp:+" . $phoneNumber,
            [
                'from' => "whatsapp:" . $twilioPhoneNumber,
                'body' => "Hii ". $name . " Thank you for registering an account on my website, your OTP code is: " . $otp,
            ]
        );
    }
}