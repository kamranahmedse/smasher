# Smasher

Turn your directory structure to array, Json, ~~XML or YML~~ and vice versa.

Smasher is a utility that lets you **get a JSON, array, ~~XML or YML~~ representation from your directory structure**, or **use the specified representations to create the directory structure**

When you *smash* a directory, all the subdirectories, files, symlinks are converted to the representation that you need and when you *build* the representaion is processed to create the specified structure i.e. directories, files and symlinks are automatically created.

## Installation
The recommended way of installation is using composer. Update your project's `composer.json` file and add the following:

```
{
    "require": {
        "kamranahmedse/smasher": "*"
    }
}
```

And run `composer install` or simply run 

```
composer require kamranahmedse/smasher
```

## How to use?

Currently `json` and `array` are the only supported representations however the support for the `xml` and `yml` representations is on it's way. However if you are in a hurry, I will show you how easy it is to do that in a moment.

Let's stick to the topic, how to use, for now.

**Introducing the classes** First things first, introduce the classes that we are going to use, in your scope.

```php
// Introduce the classes into your scope
use KamranAhmed\Smasher\Scanner;
use KamranAhmed\Smasher\JsonResponse;
```

**Smashing a directory** Generating JSON representation from the directory

```
// Instantiate the scanner class while passing the object
// of which type you need the response. Currently there is
// only JsonResponse that we have so..
$scanner = new Scanner(new JsonResponse());

// Scan the directory and return the JSON for the available content
$dirJson = $scanner->scan('/directory/to-scan');

// Or you can provide the path at which the json representation should
// be stored i.e.
// $scanner->scan('directory/to-scan', 'output/to-scan.json');

```

**..back to directory structure** Turning the JSON representation back to directory structure

```
// Instantiate the scanner class while passing the object
// of which representation that you are going to use. We are going
// to convert JSON back to directory structure
$scanner = new Scanner(new JsonResponse());

// Specify the path where you need to populate the content in JSON
// Let's populate the directory structure in the JSON representation
// inside the output directory
$scanner->populate('output/', 'path/to/representation/to-use.json');
```



