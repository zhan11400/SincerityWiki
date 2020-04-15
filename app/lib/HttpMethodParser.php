<?php


namespace app\lib;



use League\CommonMark\Block\Element\Document;
use League\CommonMark\Block\Parser\BlockParserInterface;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;
use League\CommonMark\Util\RegexHelper;

class HttpMethodParser implements BlockParserInterface
{
    const REGEXP_DEFINITION = '/^(GET|POST|PUT|DELETE|HEAD|OPTIONS|TRACE):(\/[^\s]*$)/';
    public function parse(ContextInterface $context, Cursor $cursor):bool
    {
        $container = $context->getContainer();

        if(!$container instanceof Document){
            return false;
        }

        $lines = $cursor->getLine();
        if (empty($lines)) {
            return false;
        }

        $match = RegexHelper::matchAll(self::REGEXP_DEFINITION, $cursor->getLine(), $cursor->getNextNonSpacePosition());

        if (empty($match) || count($match) !== 3) {
            return false;
        }


        /*$httpMethod = new HttpMethodBlock($match[1],$match[2]);

        $context->addBlock($httpMethod);*/
        $context->setBlocksParsed(true);

        return true;
    }
}