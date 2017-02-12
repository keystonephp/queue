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

## Requirements

PHP 7.0 and above is required.

## Getting started

Install the library with Composer.

```bash
composer require keystone/queue
```

Create a message class for the task.

```php
use Keystone\Queue\Message;

class HardMessage implements Message
{
    public $name;
    public $count;

    public function __construct(string $name, int $count = 5)
    {
        $this->name = $name;
        $this->count = $count;
    }

    public function getKey(): string
    {
        return 'hard';
    }
}
```

Create a worker class capable of processing the message.

```php
class HardWorker
{
    public function process(HardMessage $message)
    {
        // Do some work to process the message.
    }
}
```

Publish a message within your application.

```php
use Keystone\Queue\Publisher;

$publisher = new Publisher(...);
$publisher->publish(new HardMessage('Billy', 12));
```

Consume the messages in a long running process.

```php
use Keystone\Queue\Consumer;
use Keystone\Queue\Provider;

$provider = new Provider(...);
$consumer = new Consumer($provider, ...);
$consumer->consume();
```

## Credits

- [Tom Graham](https://github.com/tompedals) (maintainer)
- [Mike Perham](https://github.com/mperham) for his work on [Sidekiq](https://github.com/mperham/sidekiq)
- [Henrik Bjørnskov](https://github.com/henrikbjorn) for his work on [Bernard](https://github.com/bernardphp/bernard)
- [Olivier Dolbeau](https://github.com/odolbeau) for his work on [Swarrot](https://github.com/swarrot/swarrot)

## License

Released under the MIT Licence. See the bundled LICENSE file for details.
