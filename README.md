# Smasher - Smash your directories 

> Turn your directory structure to JSON, ~~XML or YML~~ and vice versa.

[![Build Status](https://travis-ci.org/kamranahmedse/smasher.svg?branch=master)](https://travis-ci.org/kamranahmedse/smasher)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kamranahmedse/smasher/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kamranahmedse/smasher/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/kamranahmedse/smasher/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kamranahmedse/smasher/?branch=master)

## Introduction

Smasher is a php utility that lets you **get a JSON, ~~XML or YML~~ representation from your directory structure**, or **use the specified representations to create the directory structure**

When you *smash* a directory, all the subdirectories, files, symlinks are converted to the representation that you specify and when you *build*, the representaion is processed to create the specified structure i.e. directories, files and symlinks are automatically created.

> ..all the subdirectories, files, symlinks are converted to the representation that you need ...and back

## Where to Use?

Originally it was written for a friend to let him use it in the cloud formation scripts. However you can use it wherever you want. 

Here are some of the ideas to get you started. For example, you can use it:

- Where you need some sort of virtual filesystem. 
- When parsing several directories, accessing filesystem directly and iterating through the directories means more memory usage and consumption of resources. 
- To index your directories and easily locate the place you are looking for. 
- Or may be you can use it to search files or folders based on some keywords.

I would love to know how you end up using it.

## Requirements

php >= 5.4.0 is required

## Installation
The recommended way of installation is using composer. Update your project's `composer.json` file and add the following:

```json
{
    "require": {
        "kamranahmedse/smasher": "*"
    }
}
```

And run `composer install` or simply run 

```bash
composer require kamranahmedse/smasher
```

## Getting Started

Currently `json` and `array` are the only supported representations however the support for the `xml` and `yml` representations is on it's way. However if you are in a hurry, I will show you how easy it is to do that in a moment.

Let's stick to the topic, how to use, for now.

**Introducing the classes** First things first, introduce the classes that we are going to use, in your scope.

```php
// Introduce the classes into your scope
use KamranAhmed\Smasher\Scanner;
use KamranAhmed\Smasher\JsonResponse;
```

**Smashing a directory** Generating JSON representation from the directory

```php
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

```php
// Instantiate the scanner class while passing the object
// of which representation that you are going to use. We are going
// to convert JSON back to directory structure
$scanner = new Scanner(new JsonResponse());

// Specify the path where you need to populate the content in JSON
// Let's populate the directory structure in the JSON representation
// inside the output directory
$scanner->populate('output/', 'path/to/representation/to-use.json');
```

## Example

Assuming the following directory structure.

```
- output
- sample-path
    - child-item
        - grand-child
            - child-file.md         // A file with specified content
              (Some content in the child file.)
        - grand-child-sibling
            - empty-file            // A file having no content
    - child-sibling
        - nothing-in-this-file.txt  // A file having no content
    - child-sibling-2
        - sibling-child
            - empty-file            // A file with no content
            - some-link
            - some-file
             (Text inside some-file)
```

If I want to generate the JSON representation for the `sample-path` and save it inside `output` directory

```php
use KamranAhmed\Smasher\Scanner;
use KamranAhmed\Smasher\JsonResponse;

$scanner = new Scanner(new JsonResponse);
$scanner->scan('sample-path', 'output/sample-path.json');
```

This will create the json file in `output/sample-path.json` with the representation similar to the following:

![Image of JSON](http://i.imgur.com/ZN5cWAY.png)

Also note that: `@` symbol in the beginning of a key represents the property and the keys without the `@` symbol represents a directory. If you'd like to look at the full JSON representation, [have a look at this file](https://raw.githubusercontent.com/kamranahmedse/smasher/master/tests/data/scanned-samples/scanned-json.json)


### Extending to support other formats

In order to extend `smasher` for other formats, all you have to do is create a response class by implementing the `KamranAhmed\Smasher\Contracts\ResponseContract` and pass the instance of that class to the `Scanner` object

```php
class SuperResponse implements ResponseContract {

    /**
     * Formats the passed data for example a `JsonResponse` will encode to json, `XMLResponse`
     * will encode to xml etc
     * @param  array $data The data which is to be encoded
     * @return string
     */
    public function encode($data) {
        // Put your logic to convert a nested array to 
        // *super response format* here and return the resulting string
    }
    
    /**
     * Decodes the passed string and creates array from it
     * @param  string $response The existing response which is to be decoded to array
     * @return array
     */
    public function decode($response) {
        // Put your logic to convert the $response string to
        // to a nested array, like the one you encoded, and return
        // the array
    }
}


// Then pass it to scanner object
$scanner = new Scanner(new SuperResponse);

// Directory structure will be transformed to the format you need and 
`output/to-scan.super` file will be created
$scanner->scan('path/to-scan', 'output/to-scan.super');

// To create the directory structure from the response.
$scanner->populate('output/', 'to-scan.super');
```

### Contributing

- Report any bugs
- Suggestions for improvement and any additional functionality
- Add support for additional formats and open a pull request
- Enhancing or improve the available functionality

### Feedback

I'd love to hear what you have to say. Please open an issue for any feature requests that you may want or the bugs that you notice. Also you can contact me at kamranahmed.se@gmail.com or you can also find me at twitter [@kamranahmed_se](http://twitter.com/kamranahmed_se)



