# plg_system_rgaac3rb

![](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)
![](https://img.shields.io/badge/Joomla!-3.x-blue.svg?style=flat-square)

Plugin système pour le template c3rbrgaa.

#### Fonctionnalités Bootstrap : *[Active par défaut]*
Le plugin remplace la librairie de joomla 3 JHtmlBootstrap basée sur la version 2 du bootstrap par la librairie située dans le template [rgaac3rb](https://github.com/c3rb-org/template_RGAA_C3rb) `rgaac3rb\libraries\cms\html\bootstrap.php` adaptée à la version 3 du bootstrap.

#### Fonctionnalités LESS : *[Inactive par défaut]*
Si l'option est activé et que vos fichiers LESS subissent des modifications, le plugin va recompiler le CSS au premier rafraichissement du site.
Il va également mettre en cache les fichiers générés et recompilera uniquement le CSS si les fichiers LESS subissent une modification.

Une option est disponible pour prendre en compte des valeurs de variables less dans les paramètres du template. Pour cela vous devrez l'activer dans les paramètres du plugin et nommer vos champs en suivant un motif définit :
```xml
<field name="*_lessvar_{nom-variable-less}" type="color|text|list|.." default="#000000" description="xxx" label="xxx" />
```

> Le plugin va modifier les chemins des images, polices, etc par rapport au dossier où sera enregistré le fichier CSS et non par rapport au fichier LESS.

**Attention :** Le template [rgaa_c3rb](https://github.com/c3rb-org/template_RGAA_C3rb) utilise [Bootstrap 3](https://github.com/twbs/bootstrap) et non Bootstrap 2 prévut par [Joomla! 3](https://github.com/joomla/joomla-cms/). Vous devrez donc veiller à utiliser le compilateur `lessphp`.

#### :exclamation: En cas de clone
Si vous clonez le dépôt, vous devrez installer les dépendances (qui sont automatiquement fournit avec les archives "Release").
Pour cela, rien de plus simple.
Vous devez au préalable avoir installé [Composer](https://getcomposer.org/download/) sur votre poste.
Ensuite, rendez-vous dans le dossier du plugin avec votre invité de commande et lancer la commande suivante :
`composer install` ou `composer update` si vous souhaitez mettre à jour.