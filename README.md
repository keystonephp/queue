# Keystone Queue

[![Build Status](https://travis-ci.org/keystonephp/queue.png)](https://travis-ci.org/keystonephp/queue)

A PHP library to create and process background tasks with any queueing service.

Supported queue services:

* [x] [AWS SQS](https://aws.amazon.com/sqs)
* [ ] [RabbitMQ](https://www.rabbitmq.com)

## Installation

Install via Composer

```bash
composer require keystone/queue
```

## Usage

Define the task message.

```php
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
    }
}
```

Publish the message.

```php
$publisher->publish(new TestMessage('Billy'));
```
