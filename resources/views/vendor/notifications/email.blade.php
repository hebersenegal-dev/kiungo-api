<x-mail::message>

{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# Whoops! / Oups !
@else
# Hello! / Bonjour !
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- French Translation --}}
@foreach ($introLines as $line)
{{-- You can use a translation file or hardcoded translations here --}}
{{-- Example: assuming $line contains "Please verify your email." --}}
@switch($line)
    @case('Please click the button below to verify your email address.')
        Cliquez sur le bouton ci-dessous pour vérifier votre adresse e-mail.
        @break
    @default
        {{-- Optional fallback or log missing translations --}}
@endswitch

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }} / {{ __('Vérifier l\'adresse email') }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- French Outro Lines --}}
@foreach ($outroLines as $line)
@switch($line)
    @case('If you did not create an account, no further action is required.')
        Si vous n'avez pas créé de compte, aucune autre action n'est requise.
        @break
    @default
@endSwitch

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
Regards, / Cordialement,<br>
{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
If you're having trouble clicking the "**{{ $actionText }}**" button, copy and paste the URL below into your web browser:
Si vous ne parvenez pas à cliquer sur le bouton "**{{ $actionText }}**", copiez et collez l'URL ci-dessous dans votre navigateur :

<span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset

</x-mail::message>
