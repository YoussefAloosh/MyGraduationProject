{{-- resources/views/emails/verification-code.blade.php --}}
@component('mail::message')
# Email Verification

Your verification code is:

@component('mail::panel')
# {{ $code }}
@endcomponent

This code expires in **10 minutes**.

Thanks,
{{ config('app.name') }}
@endcomponent