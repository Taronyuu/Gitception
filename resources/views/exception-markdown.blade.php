- Host: {{ $data['host'] }}
- URL: [{{ $data['method'] }}] {{ $data['fullUrl'] }}
- Exception: {{ $data['class'] }}
- Error: {{ $data['exception'] }}
- File: {{ $data['file'] }} ({{ $data['line'] }})

***

Stacktrace:

~~~~
{{ $data['error'] }}
~~~~

@foreach($data['storage'] as $key => $value)

***

{{ $key }}:

~~~~

<?php $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($value)); ?>
@foreach($it as $key => $item)
{{ $key }} => {{ str_replace('\n', '', $item) }}
@endforeach

~~~~

@endforeach