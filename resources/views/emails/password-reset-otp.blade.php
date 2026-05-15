{{-- resources/views/emails/password-reset-otp.blade.php --}}
@component('mail::message')
# Password Reset

Your OTP code is:

@component('mail::panel')
# {{ $otp }}
@endcomponent

This code expires in **5 minutes**.

Thanks,
{{ config('app.name') }}
@endcomponent