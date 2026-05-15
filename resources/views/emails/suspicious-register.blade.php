{{-- resources/views/emails/suspicious-register.blade.php --}}
@component('mail::message')
# Suspicious Activity Detected

Too many failed verification attempts for **{{ $user->email }}**.

Account has been deleted for security reasons.

Thanks,
{{ config('app.name') }}
@endcomponent