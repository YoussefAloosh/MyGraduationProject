{{-- resources/views/emails/suspicious-reset.blade.php --}}
@component('mail::message')
# Suspicious Activity Detected

Too many failed reset attempts for **{{ $user->email }}**.

Account has been locked for **60 minutes**.

Thanks,
{{ config('app.name') }}
@endcomponent