<?php

namespace Knuckles\Scribe\Extracting\Strategies\BodyParameters;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;
use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\FindsFormRequestForMethod;
use Knuckles\Scribe\Extracting\ParsesValidationRules;
use Knuckles\Scribe\Extracting\Strategies\Strategy;
use Knuckles\Scribe\Tools\ConsoleOutputUtils as c;
use Lorisleiva\Actions\Action;
use ReflectionClass;
use ReflectionFunctionAbstract;

class GetFromLaravelAction extends Strategy {

    use ParsesValidationRules;

    protected string $customParameterDataMethodName = 'bodyParameters';

    public function __invoke(ExtractedEndpointData $endpointData, array $routeRules): ?array {
        if(!$this->isLaravelActionMeantForThisStrategy($endpointData->controller)) {
            return [];
        }

        return $this->getParametersFromAction($endpointData->controller, $endpointData->method, $endpointData->route);

    }

    protected function getParametersFromAction(ReflectionClass $reflection_class, ReflectionFunctionAbstract $method, $route = null): array {
        $className = $reflection_class->getName();
        /** @var \App\Actions\Action $action */
        $action = new $className;

        if(!$reflection_class->hasMethod('rules')) {
            return [];
        }

        $parametersFromAction = $this->getParametersFromValidationRules(
            $this->getActionValidationRules($action),
            $this->getCustomParameterData($action)
        );

        return $this->normaliseArrayAndObjectParameters($parametersFromAction);
    }

    protected function getCustomParameterData($action)
    {
        if (method_exists($action, $this->customParameterDataMethodName)) {
            return call_user_func_array([$action, $this->customParameterDataMethodName], []);
        }

        c::warn("No {$this->customParameterDataMethodName}() method found in " . get_class($action) . ". Scribe will only be able to extract basic information from the rules() method.");

        return [];
    }

    protected function getActionValidationRules($action) {
        return call_user_func_array([$action, 'rules'], []);
    }

    protected function isLaravelActionMeantForThisStrategy(ReflectionClass $reflection_class) {
        return app($reflection_class->getName()) instanceof Action;
    }

}
