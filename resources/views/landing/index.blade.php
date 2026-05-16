@php
    $html = $landing->html_content;
    if ($landing->favicon) {
        $faviconUrl = Voyager::image($landing->favicon);
        $faviconTag = '<link rel="icon" href="' . $faviconUrl . '">';
        // Inject before </head> if it exists, otherwise just prepend it
        if (stripos($html, '</head>') !== false) {
            $html = str_ireplace('</head>', $faviconTag . "\n</head>", $html);
        } else {
            $html = $faviconTag . "\n" . $html;
        }
    }
@endphp
{!! $html !!}
