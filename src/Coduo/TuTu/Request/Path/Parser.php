<?php

namespace Coduo\TuTu\Request\Path;

use Symfony\Component\HttpFoundation\Request;

class Parser
{
    public function extractPlaceholders(Request $request, $pathPattern)
    {
        preg_match_all('#\{\w+\}#', $pathPattern, $placeholders, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        if (!count($placeholders)) {
            return array();
        }

        $placeholderNames = array();
        foreach ($placeholders as $placeholderMatch) {
            $placeholder = $placeholderMatch[0][0];
            $placeholderNames[] = substr($placeholder, 1, -1);
            $pathPattern = str_replace($placeholder, '__PLACEHOLDER__', $pathPattern);
        }

        $pathPattern = '/^' . str_replace('__PLACEHOLDER__', '([^\/]*)', preg_quote($pathPattern, '/')) . '$/i';

        if (0 === preg_match($pathPattern, $request->getPathInfo(), $matches)) {
            return array();
        }

        return array_combine(
            $placeholderNames,
            array_slice($matches, 1, count($placeholders))
        );
    }
}
