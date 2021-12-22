@if($authenticated)@component('scribe::components.badges.base', ['colour' => "darkred", 'text' => '🔒 Requer autenticação "'.$guard.'"'])
@endcomponent
@endif
