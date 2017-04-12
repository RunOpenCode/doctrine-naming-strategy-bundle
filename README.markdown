# Doctrine naming strategy bundle

[![Packagist](https://img.shields.io/packagist/v/RunOpenCode/doctrine-naming-strategy-bundle.svg)](https://packagist.org/packages/runopencode/doctrine-naming-strategy-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/RunOpenCode/doctrine-naming-strategy-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/doctrine-naming-strategy-bundle/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/RunOpenCode/doctrine-naming-strategy-bundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/doctrine-naming-strategy-bundle/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/RunOpenCode/doctrine-naming-strategy-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/doctrine-naming-strategy-bundle/?branch=master)
[![Build Status](https://travis-ci.org/RunOpenCode/doctrine-naming-strategy-bundle.svg?branch=master)](https://travis-ci.org/RunOpenCode/doctrine-naming-strategy-bundle)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/f6c58d45-2390-4540-9909-c5fb66ed1b62/big.png)](https://insight.sensiolabs.com/projects/f6c58d45-2390-4540-9909-c5fb66ed1b62)

Default Symfony2 Doctrine naming strategy `doctrine.orm.naming_strategy.underscore` for Entity tables is really great
and we usually use it without any modifications. However, generated table names sometimes can be too ambiguous and 
conflicting, especially if vendor bundles are used, or there is a possibility to reuse some of the code from previous projects.

Common practice is, as always, to take over the control of setting names for Entity tables explicitly.

**However, this can be really painful for lazy developers.**

## Modifying naming strategy to the rescue

In Doctrine2, it is quite possible with ease to take over the control of the naming strategy (for details see this Doctrine
["Implementing a NamingStrategy"](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/namingstrategy.html))
article), while Symfony2 in process of construction of Doctrine service, fetch naming strategy implementation as a service
(see this [Stackowerflow question and answer](http://stackoverflow.com/questions/12702657/how-to-configure-naming-strategy-in-doctrine-2)).

In that matter, the purpose of this bundle is to provide you with additional naming strategies which would allow you to 
keep good quality naming convention for your Entity tables, prevent table name conflicts and still be lazy about it.

## Naming strategies within the bundle

Provided naming strategies within the bundles are:

- `runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix`: Extension of default, underscored, naming strategy, which will add a bundle name prefix
                                                                          to the generated table names (without "bundle" in prefix).
- `runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix`: Extension of default, underscored, naming strategy, which will add a configured prefix
                                                                                   to the generated table names of the Entities based on its namespace. 
- `runopencode.doctrine.orm.naming_strategy.underscored_namer_collection`: Underscored namer collection is collection of several naming strategies, and one default naming strategy.
                                                                 Default naming strategy will define default name, and then others namers are consulted. 
                                                                 First namer in collection that provides different name from default one finally determines name.
                                                                 This will allow you to mix naming strategies, and in conjunction with white and black lists of provided namers
                                                                 you can fine tune naming process of your tables in batch.
                         
## Installing the bundle
                         
You can install this bundle by using composer:
                         
    php composer require "runopencode/doctrine-naming-strategy-bundle"                            

or you can add bundle name to your `composer.json` and execute `php composer update` command.
                               
After that, all you need is to add the bundle to your `AppKernel.php`:
                               
    class AppKernel extends Kernel 
    {
        public function registerBundles()
        {
            $bundles = array(
                [... YOUR BUNDLES...],
                new RunOpenCode\Bundle\DoctrineNamingStrategy\DoctrineNamingStrategyBundle()
            );
                
            return $bundles;
        }
    }                   

And in your project `config.yml` you should define which naming strategy you would like to use:
                
    doctrine:
        orm:
            naming_strategy: [SERVICE NAME OF NAMING STRATEGY THAT YOU WOULD LIKE TO USE]
                                      
**NOTE**: Adding this bundle to your project make sense only at the very beginning of the project, **PRIOR** to materialization of the 
tables into database. Otherwise, it will give you a quite an issue if you do not use properly namers and their possibility to define white and black lists.

## Naming strategies configuration options

### Options for `runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix`

- `case`: Optional, enum, possible values: `lowercase` or `uppercase`. Default is `lowercase`.
- `map`: Optional, array. List of bundle names and desired prefixes to use. Otherwise, namer will use full bundle name without "Bundle" at the end of the bundle name. This is quite useful 
         if you are using, per example, MySQL which has table name limit to up to 60 chars.
- `whitelist`: Optional, array. List of bundles for which prefix to the table name should be added.
- `blacklist`: Optional, array. Oposite of whitelist, list of bundles for which prefix to the table name should not be added.
- `joinTableFieldSuffix`: Optional, boolean. Whether to add field name as suffix to join table name. It is very useful to lazy developers when they have multiple many-to-many
                          relations between same entities, so they do not have to set table name manually because of table name collisions. Default is true. 
         
**NOTE:** You can either use `whitelist` or `blacklist` or none, but never booth.
         
#### Configuration example
         
    runopencode_doctrine_naming_strategy:
        underscored_bundle_prefix:
            case: lowercase
            map:
                MyLongNameOfTheBundle: my_prefix
                MyOtherLongNameOfTheBundle: my_prefix_2
            blacklist:
                - DoNotPrefixThisBundle         


### Options for `runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix`

- `case`: Optional, enum, possible values: `lowercase` or `uppercase`. Default is `lowercase`.
- `map`: Required, array. Map of FQCNs prefixes and table prefixes to use when namer stumbles upon Entity class under given FQCN prefix.
- `whitelist`: Optional, array. List of FQCNs prefixes for which prefix to the table name should be added.
- `blacklist`: Optional, array. Oposite of whitelist, list of FQCNs prefixes for which prefix to the table name should not be added.
- `joinTableFieldSuffix`: Optional, boolean. Whether to add field name as suffix to join table name. It is very useful to lazy developers when they have multiple many-to-many
                          relations between same entities, so they do not have to set table name manually because of table name collisions. Default is true.
         
**NOTE:** You can either use `whitelist` or `blacklist` or none, but never booth.

#### Configuration example
         
    runopencode_doctrine_naming_strategy:
        underscored_class_namespace_prefix:
            case: uppercase
            map:
                My\Class\Namespace\Entity: my_prefix
            blacklist:
                - My\Class\Namespace\Entity\ThisShouldBeSkipped
                - My\Class\Namespace\Entity\ThisShouldBeSkippedAsWell

### Options for `runopencode.doctrine.orm.naming_strategy.underscored_namer_collection`

- `default`: Optional, default namer to use. Default value is Symfony default namer for ORM, `doctrine.orm.naming_strategy.underscore`.
- `namers`: List of namers to use for proposing new, different name from name which was provided by default namer. Note that first different proposal wins.
- `joinTableFieldSuffix`: Optional, boolean. Whether to add field name as suffix to join table name. It is very useful to lazy developers when they have multiple many-to-many
                          relations between same entities, so they do not have to set table name manually because of table name collisions. Default is true.
 
 
#### Configuration example

    runopencode_doctrine_naming_strategy:         
        underscored_namer_collection:
            default: doctrine.orm.naming_strategy.underscore
            namers:
                - runopencode.doctrine.orm.naming_strategy.underscored_class_namespace_prefix
                - runopencode.doctrine.orm.naming_strategy.underscored_bundle_prefix            

## Known issues

- Some DBMS have certain limitation in regards to number of characters in table and/or column names (per example,
MySQL which allows 64 chars, [read this article for details](https://dev.mysql.com/doc/refman/5.7/en/identifiers.html)).
Adding long prefixes can easily breach that limitation.
