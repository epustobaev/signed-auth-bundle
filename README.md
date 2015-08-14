# SignedAuthBundle

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/bc1d329f-4dcf-436b-8c95-994768df6b7b/big.png)](https://insight.sensiolabs.com/projects/bc1d329f-4dcf-436b-8c95-994768df6b7b)

## About

The SignedAuthBundle allows you to use token with hashed request parameters and secret key for authentication in your Symfony 2 project.

## Features

* Token can be provided with header or GET|POST parameter
* Configurable hash params: secret key getter, hash string concatenation delimiter, token delimiter, token key name
* Ability to sign params from request(uri, host, etc), headers and query(POST and GET)

## Installation

Require the `epustobaev/signed-auth-bundle` package in your composer.json and update your dependencies.

    $ composer require epustobaev/signed-auth-bundle

Add the SignedAuthBundle to your application's kernel:

    public function registerBundles()
    {
        $bundles = array(
            ...
            new Dendy\SignedAuthBundle\DendySignedAuthBundle(),
            ...
        );
        ...
    }


## Configuration

Example uses orm user provider, token in request header "x-auth", sign params from headers, query and request, 
hash algorithm md5 and default delimiters.
Example token value: `username:ec1cef72d94b43cc96fc8a866f6e19d3`.

 
```yaml
security:
    providers:
        some_provider:
            entity:
                class: Namespace\Bundle\SomeBundle\Entity\SomeUser
                property: name
                manager_name: default
    firewalls:
        ## some other
        signed_secured:
            pattern:   ^/api/
            stateless: true
            provider: some_provider
            signed:
                auth_type: header
                request_key: x-auth
                token_delimiter: ':'
                data_delimiter: '|'
                hash_alg: 'md5'
                secret_getter: 'getAuthSecret'
                signed_params:
                    headers: ['Host', 'User-Agent']
                    query: ['username']
                    request: ['requestUri']
```

`auth_type` - default value is 'request' - get token value from GET or POST, in opposite 'headers' means that the token is provided in request headers.

`request_key` - default value is 'sign', otherwise can be any string value.

`token_delimiter` - default value is ':', otherwise can be any string value.

`data_delimiter` - default value is ':', otherwise can be any string value.

`hash_alg` - default value is 'md5', see accepted values http://php.net/manual/ru/function.hash-algos.php

`secret_getter` - method of user object to get secret key.

`signed_params` - signed values configuration, getting data from Symfony\Component\HttpFoundation\Request instance.

`signed_params[headers]` - array of request headers to sign(`$request->headers->get('Host')`). 

`signed_params[query]` - array of request query params(`$request->get('Host')`)

`signed_params[request]` - array of request query params(`$request->getRequestUri()`)
