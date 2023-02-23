<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RingkasHTML
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ((strtolower(strtok($response->headers->get('Content-Type'), ';')) === 'text/html') || (is_object($response) && $response instanceof Response))
        {
            $html = $response->getContent();

            $whiteSpaceRules = [
                '/(\s)+/s' => '\\1',// shorten multiple whitespace sequences
                "#>\s+<#" => ">\n<",  // Strip excess whitespace using new line
                "#\n\s+<#" => "\n<",// strip excess whitespace using new line
                '/\>[^\S ]+/s' => '>',
                // Strip all whitespaces after tags, except space
                '/[^\S ]+\</s' => '<',// strip whitespaces before tags, except space
                /**
                 * '/\s+     # Match one or more whitespace characters
                 * (?!       # but only if it is impossible to match...
                 * [^<>]*   # any characters except angle brackets
                 * >        # followed by a closing bracket.
                 * )         # End of lookahead
                 * /x',
                 */
    
                //Remove all whitespaces except content between html tags.
                //MOST DANGEROUS
                //            '/\s+(?![^<>]*>)/x' => '',
            ];
            $commentRules = [
                "/<!--.*?-->/ms" => '',// Remove all html comment.,
            ];
            $replaceWords = [
                //OldWord will be replaced by the NewWord
                // OldWord <-> NewWord DO NOT REMOVE THIS LINE. {REFERENCE LINE}
                //'/\bOldWord\b/i' =>'NewWord'
            ];
            $allRules = array_merge(
                $replaceWords,
                $commentRules,
                $whiteSpaceRules
            );

            $html = preg_replace(
                array_keys($allRules),
                array_values($allRules),
                $html
            );
    
            $response->setContent($html);
        }
        
        return $response;
    }
}
