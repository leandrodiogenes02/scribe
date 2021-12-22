<?php

namespace Knuckles\Scribe\Extracting\Strategies\Postman;

use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\RouteDocBlocker;
use Knuckles\Scribe\Extracting\Strategies\Strategy;
use Mpociot\Reflection\DocBlock;
use Mpociot\Reflection\DocBlock\Tag;

class GetPostmanEventsStrategy extends Strategy {

    public function __invoke(ExtractedEndpointData $endpointData, array $routeRules): array {
        $docBlocks      = RouteDocBlocker::getDocBlocksFromRoute($endpointData->route);
        $methodDocBlock = $docBlocks['method'];
        $classDocBlock  = $docBlocks['class'];

        $middlewares = $endpointData->route->getAction()['middleware'] ?? [];

        return $this->getMetadataFromDocBlock($methodDocBlock, $classDocBlock, $middlewares);
    }


    public function getMetadataFromDocBlock(DocBlock $methodDocBlock, DocBlock $classDocBlock, array $middlewares = []): array {
        return [
            'postmanEvent' => $this->getPostmanEventFromDocBlock($classDocBlock->getTags()) ?: $this->getPostmanEventFromDocBlock($methodDocBlock->getTags()),
        ];
    }


    /**
     * @param array $tags Tags in the method doc block
     *
     * @return Tag
     */
    protected function getPostmanEventFromDocBlock(array $tags) {
        $event = collect($tags)
            ->first(function($tag) {
                return $tag instanceof Tag && strtolower($tag->getName()) === 'postmanevent';
            });
        return $event ? $event->getDescription() : null;
    }

}
