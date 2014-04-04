ANSUserBundle
===============

## Installation

- Security
``` yaml
# app/config/security.yml

security:
    firewalls:
        main:
            pattern:	^/
            anonymous:	~
            form_login:
                login_path:	login
                check_path:	login_check
                # field names for the username and password fields
                username_parameter: _email
                password_parameter: _password
                #use_referer:	true
            logout:
                path:	/logout
                target:	/
            remember_me:
                key: CHANGE_IT!!! # Random key
                lifetime: 31536000 # 1 year in seconds
                path:	/
                domain:	~
                always_remember_me: true
    providers:
        main:
            entity:
                class: Name\UserBundle\Entity\User
                property: email
    encoders:
        Name\UserBundle\Entity\User:
            algorithm: sha1
            iterations: 1
            encode_as_base64: false
    role_hierarchy:
        ROLE_MODERATOR:	ROLE_USER
        ROLE_ADMIN:	[ROLE_MODERATOR, ROLE_ALLOWED_TO_SWITCH]
```

- Extend User
``` php
use ANS\UserBundle\Entity\User as BaseUser;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity()
 */
class User extends BaseUser
```

- Extend Token
``` php
use ANS\UserBundle\Entity\Token as BaseToken;

/**
 * @ORM\Table(name="token")
 * @ORM\Entity()
 */
class Token extends BaseToken
```

- Routing
``` yaml
# app/config/routing.yml

ans_user:
    resource: "@ANSUserBundle/Resources/config/user/routing.yml"
    prefix:   /
```

- Config
``` yaml
# app/config/config.yml

ans_user:
    site_name: Name.ru
    site_email: info@name.ru
    user_class: Name\UserBundle\Entity\User
    token_class: Name\UserBundle\Entity\Token
    token_ttl: 3 day
```

- Kernel
``` yaml
# app/AppKernel.php

new ANS\UserBundle\ANSUserBundle(),
```

- If UserController exists than it has to extend ANS\UserBundle\Controller\UserController

- If templates and routes have to be replaced
``` php
# Name\UserBundle\NameUserBundle.php

public function getParent()
{
    return 'ANSUserBundle';
}
```

