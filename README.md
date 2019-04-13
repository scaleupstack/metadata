# ScaleUpStack/Metadata

This library provides a flexible metadata structure for PHP classes (including methods, properties, and annotations). It is based on [schmittjoh/metadata].

The metadata is extracted from the PHP source code of the class. It reads DocBlock annotations using [ScaleUpStack/Annotations].

You will be able to register your own feature analyzers that extend the metadata based on reflection, or the provided DocBlock annotations.


## Installation

Use [Composer] to install this library:

```
$ composer require scaleupstack/annotations dev-master
$ composer require scaleupstack/metadata dev-master
```

(As ScaleUpStack/Annotations has no stable relase yet, you need to require it as well.)

## Introduction

TODO: TBD


## Current State

This library is not yet documented and still under development. It will evolve in the context of [EasyObject].


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
[ScaleUpStack/Annotations]: https://github.com/scaleupstack/annotations
[Composer]: https://getcomposer.org
[EasyObject]: https://github.com/scaleupstack/easy-object
[issue tracker]: https://github.com/scaleupstack/metadata/issues
[Git repository]: https://github.com/scaleupstack/metadata
[LICENSE.md]: LICENSE.md
[www.scaleupventures.com]: https://www.scaleupventures.com/
