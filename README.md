# Bezier
PHP cubic bezier easing class
Create a bezier easing function and get output values from 0 to 1 depending on input from 0 to 1.
## Usage

```php

$bez = new Bezier(0,0,.58,1);
$step = .5
$output = $bez->bezierEasing($step);

```
