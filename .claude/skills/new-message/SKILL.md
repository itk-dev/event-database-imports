---
name: new-message
description: Scaffold a new Symfony Messenger message + handler in the async import pipeline, wired to the async transport. Use when adding an asynchronous processing step.
disable-model-invocation: true
---

Add a new step to the asynchronous import pipeline (ADR 003). A step is a **message** (`src/Message`) + a **handler** (`src/MessageHandler`) + a **routing entry** (`config/packages/messenger.yaml`). Miss the routing and it silently runs synchronously.

## 1. Message — `src/Message/<Name>Message.php`

Carry ids, not entities (payloads are serialized to RabbitMQ; re-fetch in the handler). Most messages relate to an event and extend `AbstractEventIdMessage`:

```php
<?php

namespace App\Message;

final class <Name>Message extends AbstractEventIdMessage
{
    public function __construct(
        private readonly int $eventId,
        // ... other scalar ids/values
    ) {
        parent::__construct($this->eventId);
    }
}
```

(If it isn't event-scoped, make it a plain `final` class with readonly scalar properties instead.)

## 2. Handler — `src/MessageHandler/<Name>Handler.php`

```php
<?php

namespace App\MessageHandler;

use App\Message\<Name>Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
final readonly class <Name>Handler
{
    public function __construct(
        // inject repositories/services; add MessageBusInterface to dispatch the next step
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(<Name>Message $message): void
    {
        // Re-fetch by id. Make it idempotent — AMQP delivery is at-least-once.
        // Throw UnrecoverableMessageHandlingException for permanent failures
        // (routes to `failed` without retry); let transient errors bubble to retry.
    }
}
```

## 3. Routing — `config/packages/messenger.yaml`

Add under `routing:` so it goes to the RabbitMQ `async` transport (like every other `App\Message\*`):

```yaml
        routing:
            'App\Message\<Name>Message': async
```

## 4. Verify

- `docker compose exec -T phpfpm bin/console debug:messenger` lists the handler for the message.
- `task code-analysis:phpstan` is clean.
- Run the `messenger-handler-reviewer` agent to check idempotency, failure handling, and CQRS/index-contract concerns before opening the PR.
