<?php

class Rss
{
    /**
     * 50 most common worlds
     * @var array
     */
    public $mostCommonWords = ['the', 'be', 'to', 'of', 'and', 'a', 'in', 'that', 'have', 'I', 'it', 'for', 'not', 'on', 'with', 'he', 'as', 'you', 'do', 'at', 'this', 'but', 'his', 'by', 'from', 'they', 'we', 'say', 'her', 'she', 'or', 'an', 'will', 'my', 'one', 'all', 'would', 'there', 'their', 'what', 'so', 'up', 'out', 'if', 'about', 'who', 'get', 'which', 'go', 'me'];

    /**
     * lists of feeds
     * redirectJs string - search pattern if has redirect js
     * name string - name feed it need for connection method
     * url string - feed's url
     * @var array
     */
    public $source = [
        ['redirectJs' => 'a', 'name' => 'Workable', 'url' => 'https://workable.com/nr?l=https%3A%2F%2Fwww.theregister.co.uk%2Fsoftware%2Fheadlines.atom'],
    ];

    /**
     * patterns fpr redirects JS for versatility
     * @var array
     */
    public $reg = ['a' => '/<a(.*?)href="([^"]+)"(.*?)>/ui'];
    public $errors;
    public $cnt = 0; // count elements in one feed
    public $content = [];

    /**
     * get all feed lists from $this->source
     * @return array
     */
    public function getArraylists()
    {
        $data = [];
        foreach ($this->source as $k => $v) {
            $arr = $this->getFeedArray($v["url"], $k, $v['redirectJs']);
            if (empty($arr['error'])) {
                $data[$k]['lists'] = $this->makeArFeed($arr, $v['name']);
            } else {
                $data[$k]['error'] = $arr['error'];
            }
        }
        return $data;
    }

    /**
     * get feed array
     * @param string $url
     * @param string $k
     * @param string $redirectJs
     * @return array
     */
    public function getFeedArray(string $url, string $k, string $redirectJs = '')
    {
        $data = $result = [];
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING => "",       // handle all encodings
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:18.0) Gecko/20100101 Firefox/18.0", // something like Firefox
            CURLOPT_AUTOREFERER => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT => 120,      // timeout on response
            CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
        );
        $curl = curl_init($url);
        curl_setopt_array($curl, $options);
        $content = curl_exec($curl);
        curl_close($curl);

        if ($this->verifyXmlString($content)) {
            $data = simplexml_load_string($content);
            $json = json_encode($data);
            $data = json_decode($json, true);
            $this->content[$k] = $content;
        } elseif (!empty($redirectJs) && !empty($this->reg[$redirectJs])) {
            preg_match($this->reg[$redirectJs], $content, $matches);
            if (!empty($matches[2])) {
                $this->errors = [];
                $data = $this->getFeedArray($matches[2], $k, $redirectJs = '');
            } else {
                $data['error'] = 'Xml file error!';
            }
        } else {
            $data['error'] = 'Xml file error!';
        }
        return $data;
    }

    /**
     * connection of the necessary method
     * @param array $arr
     * @param string $name
     * @return string
     */
    public function makeArFeed(array $arr, string $name)
    {
        $result = '';
        $getMetod = 'get' . $name . 'Array';
        if (method_exists($this, $getMetod)) {
            $result = $this->$getMetod($arr);
        }
        return $result;
    }

    /**
     * get array of feed with name Workable
     * @param array $arr
     * @return array
     */
    public function getWorkableArray(array $arr)
    {
        $data = [];
        if ($arr === FALSE) {
            $data['error'] = 'Xml file error!';
        } else {
            $i = 1;
            foreach ($arr['entry'] as $k => $v) {
                if ($this->cnt && $i > $this->cnt) break;
                $data['title'] = $arr['title'];
                $data[$k]['date'] = $v['updated'];
                $data[$k]['author'] = $v['author']['name'];
                $data[$k]['link'] = $v['link']['@attributes']['href'];
                $data[$k]['title'] = $v['title'];
                $data[$k]['description'] = $v['summary'];
                $i++;
            }
        }
        return $data;
    }

    /**
     * error checking xml
     * @param string $content
     * @return bool
     */
    public function verifyXmlString(string $content)
    {
        if (empty($content)) {
            $this->errors[] = 'file is empty';
            return false;
        }

        libxml_use_internal_errors(true);
        $sxe = simplexml_load_string($content);
        if (!$sxe) {
            foreach (libxml_get_errors() as $error) {
                $this->errors[] = $error->message;
            }
            return false;
        }
        return true;
    }

    /**
     * find an array of word matches except for the 50 most used
     * @param int $k current feed key
     * @param array $where (what tags are looking for matches?)
     * @return array
     */
    public function getMostFrequentWorlds(int $k, array $where = ['title', 'summary'])
    {
        $arr = [];
        if (empty($this->content[$k])) return $arr;

        $pattern = [];
        foreach ($where as $p) {
            $pattern[] = '<' . $p . '.+<\/' . $p . '>+';
        }
        $str = $this->content[$k];
        preg_match_all('/' . implode('|', $pattern) . '/i', $str, $matches);
        $str = mb_strtolower(strip_tags(html_entity_decode(implode(' ', $matches[0]))));
        $str = preg_replace('/[^a-z]+/i', ' ', $str);
        $str = preg_replace('/\s.{1}\s/i', ' ', $str);
        $arr = array_diff(explode(' ', $str), $this->mostCommonWords);
        $arr = array_count_values($arr);
        arsort($arr);
        return $arr;
    }
}