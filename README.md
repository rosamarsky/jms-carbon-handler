# Carbon Handler

> Simple to use Carbon handler for JMS Serializer

## Installation
    composer require rosamarsky/jms-carbon-handler

## Setup
Register the Carbon Handler via the builder object:
    
```php
    $builder->configureHandlers(function (HandlerRegistry $registry) {
        $registry->registerSubscribingHandler(new CarbonHandler);
    });
```

## Usage

As annotations:
      
```php
    class SomeClass
    {
        /**
         * @Type("Carbon<'Y-m-d'>")
         */
        public $date;
    }
```
As YAML:

```yaml
    date:
      type: Carbon<'d-m-Y'>
```

As XML:

```xml
    <property name="date" xml-attribute="true" type="Carbon"/>
```