Behat Symfony2 service generator
================================

This is a [Behat](http://behat.org) extension that catches calls
to undefined Symfony services and runs PHPSpec to describe them.

Installation
------------

Just run this:

```
	composer require kix/behat-sf2-service-generator "~0.1" 
```

And then enable the extension in your `behat.yml`:

```
default:
  # ...
  extensions:
    Kix\Symfony2ServiceExtension\Symfony2ServiceExtension: ~
```

Now, when you try to access a non-existent service, you'll be asked if
you want to generate a class for it. A YAML config will be dumped on the
console for your convinience, too. Here's a quick peek at what it looks like.

```
Feature: Generating Symfony services
  Scenario: Creating a service when a ServiceNotFoundException is caught
    When I run behat
    And my step tries to get a service that doesn't exist
    Then a shiny notification is displayed asking if I want to generate it
```

```
<?php
class FeatureContext implements Context, SnippetAcceptingContext
{

	// Note I'm using Symfony2Extension here:
    use \Behat\Symfony2Extension\Context\KernelDictionary;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @When I run behat
     */
    public function iRunBehat()
    {
    	// do nothing
    }

    /**
     * @When my step tries to get a service that doesn't exist
     */
    public function myStepTriesToGetAServiceThatDoesnTExist()
    {
        $this->getContainer()->get('my_bundle.kitten_provider');
    }

}
```

And boom:

![Example](http://habrastorage.org/files/df9/b24/1bc/df9b241bc91a479fa27497303cd7fe47.png)
