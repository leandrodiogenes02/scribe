@php
    use Knuckles\Scribe\Tools\WritingUtils as u;
    /** @var  Knuckles\Camel\Output\OutputEndpointData $endpoint */
@endphp
```javascript



//------------------------------------
//------------ ApiRequest ------------
//------------------------------------

@if(count($endpoint->cleanQueryParameters))
    let params = {!! \Knuckles\Scribe\Tools\WritingUtils::printQueryParamsAsKeyValue($endpoint->cleanQueryParameters, "\"", ":", 4, "{}")
    !!};
@endif
@if(count($endpoint->cleanBodyParameters))
    let params = {!! json_encode($endpoint->cleanBodyParameters, JSON_PRETTY_PRINT)
    !!}
@endif

new ApiRequest({
url: "{{ rtrim($baseUrl, '/') }}/{{ ltrim($endpoint->boundUri, '/') }}",
method: "{{$endpoint->httpMethods[0]}}",
@if($endpoint->httpMethods[0] == "PUT")
    raw: true,
@endif
success: function(response){ }
})
@foreach($endpoint->fileParameters as $parameter => $file)
    .addParameter('{!! $parameter !!}', document.querySelector('input[name="{!! $parameter !!}"]').files[0]);
@endforeach
@if(count($endpoint->cleanQueryParameters) or count($endpoint->cleanBodyParameters))
    .addParameters(params)
@endif
.execute();


//-----------------------------------------
//------------ JAVASCRIPT puro ------------
//-----------------------------------------

const url = new URL(
    "{{ rtrim($baseUrl, '/') }}/{{ ltrim($endpoint->boundUri, '/') }}"
);
@if(count($endpoint->cleanQueryParameters))

const params = {!! u::printQueryParamsAsKeyValue($endpoint->cleanQueryParameters, "\"", ":", 4, "{}") !!};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));
@endif

@if(!empty($endpoint->headers))
const headers = {
@foreach($endpoint->headers as $header => $value)
    "{{$header}}": "{{$value}}",
@endforeach
@empty($endpoint->headers['Accept'])
    "Accept": "application/json",
@endempty
};
@endif

@if($endpoint->hasFiles())
const body = new FormData();
@foreach($endpoint->cleanBodyParameters as $parameter => $value)
@foreach( u::getParameterNamesAndValuesForFormData($parameter, $value) as $key => $actualValue)
body.append('{!! $key !!}', '{!! $actualValue !!}');
@endforeach
@endforeach
@foreach($endpoint->fileParameters as $parameter => $value)
@foreach( u::getParameterNamesAndValuesForFormData($parameter, $value) as $key => $file)
body.append('{!! $key !!}', document.querySelector('input[name="{!! $key !!}"]').files[0]);
@endforeach
@endforeach
@elseif(count($endpoint->cleanBodyParameters))
@if ($endpoint->headers['Content-Type'] == 'application/x-www-form-urlencoded')
let body = "{!! http_build_query($endpoint->cleanBodyParameters, '', '&') !!}";
@else
let body = {!! json_encode($endpoint->cleanBodyParameters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!};
@endif
@endif

fetch(url, {
    method: "{{$endpoint->httpMethods[0]}}",
@if(count($endpoint->headers))
    headers,
@endif
@if($endpoint->hasFiles())
    body,
@elseif(count($endpoint->cleanBodyParameters))
@if ($endpoint->headers['Content-Type'] == 'application/x-www-form-urlencoded')
    body: body,
@else
    body: JSON.stringify(body),
@endif
@endif
}).then(response => response.json());
```
