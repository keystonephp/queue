# Keystone Queue

[![Build Status](https://travis-ci.org/keystonephp/queue.png)](https://travis-ci.org/keystonephp/queue)

A PHP library to create and process background tasks with any queueing service.

Supported queue services:

* [x] [AWS SQS](https://aws.amazon.com/sqs)
* [ ] [RabbitMQ](https://www.rabbitmq.com)

Features:

* Compatible with any queueing service via provider/publisher interfaces.
* Middleware to hook into the processing flow (inspired by [PSR-15](https://github.com/php-fig/fig-standards/tree/master/proposed/http-middleware)).
* Route task messages to workers registered as services in [PSR-11](https://github.com/container-interop/fig-standards/blob/master/proposed/container.md)/Symfony containers.

Middleware:

* Automatically close timed out Doctrine DBAL connections.
* Automatically clear the Doctrine ORM managers to free memory.
* Limit the maximum execution time of the consumer.
* Limit the maximum number of messages a consumer will process.
* Limit the maximum amount of memory a consumer is allowed to use.
* Retry failed tasks using an exponential backoff strategy.
* Handle signals to terminate the consumer process safely.

## Installation

Install via Composer

```bash
composer require keystone/queue
```

## Usage

Create the message class for a task.

```php
use Keystone\Queue\Message;

class TestMessage implements Message
{
    public $name;

    public function __construct(name)
    {
        $this->name = $name;
    }

    public function getKey(): string
    {
        return 'test';
    }
}
```

Create the worker class.

```php
class TestWorker
{
    public function process(TestMessage $message)
    {
        // Do some work to process the message.
    }
}
```

Publish a message within your application.

```php
$publisher->publish(new TestMessage('Billy'));
```

Consume the messages in a long running process.

```php
$consumer->consume();
```

## Credits

Inspired by Sidekiq, Bernard and Swarrot.
