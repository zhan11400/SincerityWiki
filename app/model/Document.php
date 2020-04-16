<?php


namespace app\model;


use app\index\lib\HttpMethodParser;
use Carbon\Carbon;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use think\facade\Cache;
use think\Model;

class Document extends Model
{
    /**
     * 从换成中获取解析后的文档内容
     * @param int $doc_id
     * @param bool $update
     * @return bool|string
     */
    public static function getDocumnetHtmlFromCache($doc_id,$update = false)
    {
        $key = 'document.html.' . $doc_id;

        $html = null;//$update or Cache::get($key);

        if(empty($html)) {
            $document = self::getDocumentFromCache($doc_id, $update);

            if (empty($document)) {
                return false;
            }
            if(empty($document->doc_content)){
                return '';
            }

            $converter = new GithubFlavoredMarkdownConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);

            $html= $converter->convertToHtml($document->doc_content);
           // $html = markdown_converter($document->doc_content);

            $html = str_replace('class="language-','class="',$html);
            $expiresAt = Carbon::now()->addHour(12);

            Cache::set($key,$html,$expiresAt);
        }
        return $html;
    }

    /**从缓存中获取指定的文档
     * @param $doc_id
     * @param bool $update
     * @return array|bool|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getDocumentFromCache($doc_id,$update = false)
    {
        $key = 'document.doc_id.'.$doc_id;
        $document = $update or Cache::get($key);

        if(empty($document) or $update){
            $document = Document::where('doc_id',$doc_id)->find();
            $expiresAt = Carbon::now()->addHour(12);

            Cache::set($key,$document,$expiresAt);
        }
        return $document;
    }

    public static function deleteDocument($doc_id)
    {
        $documents = [];
        $doc = Document::where('doc_id',$doc_id)->find();
        if (empty($doc) === false) {
            $documents[] = $doc;
            $recursion = function ($id, $callback) use (&$documents) {
                $docs = Document::where('parent_id', '=', $id)->select();

                foreach ($docs as $doc) {
                    $documents[] = $doc;
                    $callback($doc->doc_id, $callback);
                }

            };
            $recursion($doc->doc_id, $recursion);
        }
        foreach ($documents as $document){
            $document->delete();
        }
        return true;
    }
}