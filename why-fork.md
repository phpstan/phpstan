Hello, every body. This is a huge patch. Making this PR is just a request for
comment(RFC). If this PR were acceped, I would make contribution to phpstan;
otherwise, I would maintain my own fork.

Thanks all the developer's awesome work. phpstan is awesome :+1:.

## embrace psr
- all code including test code has been fix by phpcs according psr1 and psr2
- the coding standard of cs target has been set to be psr1 & psr2
- all testcase related to file lineno has been fixed manually
- use tedivm/stash in favor of psr6

But why?
> PSR is for the whole community, and PSR is the future!

## embrace php-di/php-di
- use autowiring as much as possible
- use interface instead of tag for extension

But why?
> nette/di is awesome. However, it does not support autowiring. So for every
> class you need to be auto injected, you have to register them
> in the config.neon file. It is boring and ridiculous. By use php-di/php-di,
> what is you only need to register is these class they have 
> constructor parameters which can not be injected like array.
>
> The original implementation use tags to find the rule class and extension class.
> As we can see in the code, all the tags are just variants of their responding interface.
> So what we need to do is just register rule class and extension class 
> by their interface and fetch them by interface as well. No tag, less memory burden.
>
> And php-di/php-di implements the container-interop/container-interop, which
> has been officially deprecated in favor of PSR-11. PSR again!

## embrace option
- let --autoload-file support multiple path, so drop Nette\Loaders\RobotLoader
- add --rule option to set rules to be used
- add --exclude-rule combine with --level option to disable some rule
- add --ignore-path and drop fileExcluder dependence. Finder's customer filter is enough
- add --ignore-error option
- add --extension option
- drop -c option and neo dependence, there is no need any more

But why?
> As php has no builtin autoload standard, there are thousands upon thousands 
> autoload method has been developed. Thanks to the composer project, these chaos
> is terminating, and maybe disappeared in the near future. However, we have to
> make sure phpstan support the legacy project. So, make --autoload-file support
> multiple path will be of convinent. And the RobotLoader is useless.
>
> A new conf/config.php has been introduced, which is used for internal only.
> If a rule class does not have any scalar typehint, it will be loaded by php-di
> without any config. So if user want to enable their own rule or extension,
> what they only need is to define class implements these interface, and DO NOT
> add typehint to their __constructor. So, no need to introduce an additional
> configure file. So drop the -c option. Further, we could add option for
> internal flags like checkThisOnly, checkFunctionArgumentTypes, enableUnionTypes.
> All in all, drop the -c option.

## embrace cli
- fix process term signal delay
- drop normalPath
- move all output except source error list to stderr
- use list sytle to display error instead of table style

But why?
> Call pcntl_signal_dispatch after each file analysis to reduce signal process delay.
>
> Display the file path according to the input opiton. If user use relative path, just
> display relative path; if absolute, just absolute. Do less, do clear.
>
> Use list style to display error infomation, so we will get
>
> 1/1 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
>
>
> * Method Note::bar() should return string but returns int in tests/notAutoloaded/extension-demo.php on line 68
> * Method Note::bar() invoked with 3 parameters, 0 required in tests/notAutoloaded/extension-demo.php on line 73
> * Access to an undefined static property Note::$c in tests/notAutoloaded/extension-demo.php on line 74
>
>
> [ERROR] Found 3 errors
>
> The progressbar and the [ERROR] infomation will display into stderr and
> the error begin with * will be display into stdout. So if you want to use 
> other util to use phpstan's check result, just read its stdout and filter by
> the leading star char.
>
> But why not add some option like --xml or --json? There is no doubt that xml and
> json is more friendly for machine reading. But they are hard to read for
> human, and parsing them is not so easy. Can you parse xml or json in bash script?
> Use the list style is just enough for both machine reading and human reading.
> The list is clear and simple. Parsing the result is nothing than filter by 
> leading star char and split by "in" and "on line", the first part is 
> message, second file path, and thrid lineno. It works.
>
> The table style is a little more beautiful but more useless. Who will use phpstan?
> The phpstom user seems not likely to use phpstan, because phpstom is smart enough.
> So phpstan is only for two use case, one for ci like travis, and the other for
> developer who does not use IDE but editor like vim, emacs, etc. All these users
> are power user. And they are like to let phpstan display the error list to their
> favorite editor. So a machine reading friendly style like list style should be used.

## embrace phar
- use system tmp dir instead of rootDir/tmp

But why?
> You can use phar-composer to package all the phpstan sorce into a phar executable.
> But how? Just run composer update --no-dev and go to parent dir and run
> phar-composer build phpstan
> and you will got you phpstan.phar. Awesome.
>
> For user who do not have their own rule or extension class, use phpstan.phar
> is just enough. For user who want to develop their own rule or extension class,
> however, they have to implements some phpstan's interface, which are defined
> in the phpstan/phpstan package. They have two choice. One is require
> phpstan/phpstan for dev only. If use this metho, phpstan.phar is no more needed.
> The other choice is to just use phpstan.phar, and implement phpstan's interface
> blindly. As phpstan.phar contains these interfaces, no error will occure. But
> your IDE will display error for the unexisting phpstan interfaces.
>
> Maybe we have third choice. We can split all the interfaces need to develop
> rule and extension into and seprate package like phpstan/phpstan-interface.
> Both phpstan/phpstan and the user's own code could implements these interfaces.
