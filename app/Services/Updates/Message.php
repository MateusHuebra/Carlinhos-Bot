<?php

namespace App\Services\Updates;

use App\Models\Pattern;

class Message implements Update {

    private $textIndex;

    function __construct($textIndex) {
        $this->textIndex = $textIndex;
    }

    function handle(array $update) {
        $message = $update['message'];
        $response = null;
        $matchedPattern = null;

        $message['text'] = $this->fixTypos($update['message'][$this->textIndex]);

        $patternsQueryBuilder = Pattern::whereNotNull('regex')->take(3);

        //check if carlinhos was mentioned
        if((isset($message['reply_to_message']) && $message['reply_to_message']['from']['username'] == 'carlosbot') || strpos(strtolower($message['text']), "carlinhos") !== false || strpos(strtolower($message['text']), "@carlosbot") !== false || $message['chat']['type']=='private') {
            $patternsQueryBuilder->orderBy('need_mention', 'DESC');
        } else {
            $patternsQueryBuilder->where('need_mention', false);
        }

        $patternsQueryBuilder->orderBy('priority', 'DESC');

        $skip = 0;
        while($matchedPattern===null) {
            $patterns = $patternsQueryBuilder->skip($skip)->get();
            if($patterns->count()===0) {
                break;
            }
            foreach ($patterns as $pattern) {
                if(preg_match('/'.$pattern->regex.'/i', $message['text'], $regexMatches)) {
                    $matchedPattern = $pattern;
                    return $matchedPattern;
                }
            }
            $skip+=3;
        }
        return null;

    }

    /**
     * fix possible typos in the user's message
     * @param string $text user's message
     * @return string fixed user's message
     */
    public function fixTypos(string $text) : string {
        $text = preg_replace(['/\bo q\b/ui', '/\boq\b/ui', '/\bo quê\b/ui'], 'o que', $text);
        $text = preg_replace(['/\bn\b/ui', '/\bnao\b/ui', '/\bnn\b/ui'], 'não', $text);
        $text = preg_replace(['/\bvc\b/ui', '/\bvoce\b/ui', '/\btu\b/ui'], 'você', $text);
        $text = preg_replace(['/\bpq\b/ui', '/\bpor quê\b/ui', '/\bpor q\b/ui'], 'por que', $text);
        $text = preg_replace(['/\bta\b/ui', '/\bestá\b/ui', '/\besta\b/ui'], 'tá', $text);
        $text = preg_replace(['/\b\/play\b/ui'], 'listening for the', $text);
        $text = preg_replace(['/\bvsf[d]*\b/ui', '/\bvai s[ei] f[uo]d[eê][r]*\b/ui'], 'vai se foder', $text);
        $text = preg_replace(['/\bfdp*\b/ui', '/\b(fi[oa]?h?|filh[oa]) da puta+\b/ui'], 'filho da puta', $text);
        $text = preg_replace(['/\b([v]*tnc|tmnc)\b/ui', '/\bvai tom[aá]+[r]* no( seu)? c[uú]+\b/ui'], 'vai tomar no cu', $text);
        $text = preg_replace(['/\btmj\b/ui'], 'tamo junto', $text);
        $text = preg_replace(['/\b[o]+[p]+[a]+\b/ui', '/\b[o]+[l]+[aá]+\b/ui', '/\b[o]+[i]+[e]*\b/ui', '/\b[e]+[i]+\b/ui'], 'oi', $text);
        return $text;
    }

}