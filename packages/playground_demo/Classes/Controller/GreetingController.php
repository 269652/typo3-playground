<?php

declare(strict_types=1);

namespace Playground\Demo\Controller;

use Playground\Demo\Service\GreetingService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class GreetingController extends ActionController
{
    public function __construct(
        private readonly GreetingService $greetingService,
        private readonly Context $context,
    ) {}

    public function showAction(): ResponseInterface
    {
        /** @var \DateTimeImmutable $now */
        $now = $this->context->getPropertyFromAspect('date', 'full');

        $this->view->assign('greeting', $this->greetingService->greet((string)($this->settings['name'] ?? ''), $now));

        return $this->htmlResponse();
    }
}
