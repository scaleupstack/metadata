# ScaleUpStack/Metadata

This library provides a flexible metadata structure for PHP classes (including methods, properties, and annotations). The metadata is extracted from the PHP source code of the class.

You will be able to register feature analyzers (shipped with this library, or your own) that extend the metadata based on reflection, or the provided DocBlock annotations.

Currently these additional feature analyzers are provided:

* TypedProperties (including union types)
* VirtualMethods

This library is based on [schmittjoh/metadata]. It reads DocBlock annotations using [scaleupstack/annotations].


## Installation

Use [Composer] to install this library:

```
$ composer require scaleupstack/metadata
```


## Introduction

This library is not yet documented. But perhaps [scaleupstack/easy-object] can be helpful to find out how to use it.

TODO: to be done


## Current State

This library has been developed with a special intention in mind. It will evolve in the context of [scaeupstack/easy-object].

If you are missing anything, feel free to contact me, or create a pull request.


## Contribute

Thanks that you want to contribute to ScaleUpStack/Metadata.

* Report any bugs or issues on the [issue tracker].

* Get the source code from the [Git repository].


## License

Please check [LICENSE.md] in the root dir of this package.


## Copyright

ScaleUpVentures Gmbh, Germany<br>
Thomas Nunninger <thomas.nunninger@scaleupventures.com><br>
[www.scaleupventures.com]


[schmittjoh/metadata]: https://github.com/schmittjoh/metadata
[scaleupstack/annotations]: https://github.com/scaleupstack/annotations
[Composer]: https://getcomposer.org
[scaleupstack/easy-object]: https://github.com/scaleupstack/easy-object
[issue tracker]: https://github.com/scaleupstack/metadata/issues
[Git repository]: https://github.com/scaleupstack/metadata
[LICENSE.md]: LICENSE.md
[www.scaleupventures.com]: https://www.scaleupventures.com/
