<?php

namespace App\Services\Updates;

use App\Models\Pattern;

class Message implements Update {

    const PATTERNS_PER_PAGE = 3;
    private $textIndex;

    function __construct($textIndex) {
        $this->textIndex = $textIndex;
    }

    function handle(array $update) {
        $message = $update['message'];
        $matchedPattern = null;

        $message['text'] = $this->fixTypos($update['message'][$this->textIndex]);

        $patternsQueryBuilder = Pattern::whereNotNull('regex')->take(self::PATTERNS_PER_PAGE);

        if($this->wasCarlinhosMentioned($message)) {
            $patternsQueryBuilder->orderBy('need_mention', 'DESC');
        } else {
            $patternsQueryBuilder->where('need_mention', false);
        }

        $patternsQueryBuilder->orderBy('priority', 'DESC');
        //echo $patternsQueryBuilder->toSql();
        //die;

        $skip = 0;
        while($matchedPattern===null) {
            $patterns = $patternsQueryBuilder->skip($skip)->get();
            file_put_contents('php://stderr', "\n\n collected patterns: ".$patterns->count());
            if($patterns->count()===0) {
                break;
            }
            foreach ($patterns as $pattern) {
                file_put_contents('php://stderr', "\n - testing ".$pattern->name);
                if(preg_match('/'.$pattern->regex.'/i', $message['text'], $regexMatches)) {
                    $chance = rand(1, 100);
                    file_put_contents('php://stderr', " <– pattern matches! chance:".$pattern->chance.' draw:'.$chance);
                    if($pattern->chance === 100 || $chance <= $pattern->chance) {
                        file_put_contents('php://stderr', ' ✓');
                        $matchedPattern = $pattern;
                        return $matchedPattern;
                    } else {
                        file_put_contents('php://stderr', ' ✕');
                    }
                }
            }
            $skip+=self::PATTERNS_PER_PAGE;
        }

        file_put_contents('php://stderr', "\n\n ending search");
        return null;

    }

    /**
     * fix possible typos in the user's message
     * @param string $text user's message
     * @return string fixed user's message
     */
    private function fixTypos(string $text) : string {
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

    private function wasCarlinhosMentioned(array $message) {
        if(
            (
                isset($message['reply_to_message'])
                &&
                (
                    $message['reply_to_message']['from']['username'] == 'carlosbot'
                    ||
                    $message['reply_to_message']['from']['username'] == 'testesubstitutorosebot'
                )
            )
            ||
            strpos(strtolower($message['text']), "carlinhos") !== false
            ||
            strpos(strtolower($message['text']), "@carlosbot") !== false
            ||
            $message['chat']['type']=='private'
        ) {
            return true;
        }
        return false;
    }

}