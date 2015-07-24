# plg_system_rgaac3rb
Plugin système pour le template c3rbrgaa.

Le plugin remplace la librairie de joomla 3 JHtmlBootstrap basée sur la version 2 du bootstrap par la librairie située dans le template <a href="https://github.com/c3rb-org/template_RGAA_C3rb">rgaac3rb</a> (rgaac3rb\libraries\cms\html\bootstrap.php) adaptée à la version 3 du bootstrap.

#### Fonctionnalités LESS : *Désactivé par défaut*
Si vos fichiers LESS subissent des modifications, le plugin va recompiler le CSS au premier rafraichissement du site.
Il va également les placer en cache et ne sera appelé que si les fichiers less sont modifiés.

**Ateention :** Le template [rgaa_c3rb](https://github.com/c3rb-org/template_RGAA_C3rb) utilise [Bootstrap 3](https://github.com/twbs/bootstrap) et non Bootstrap 2 prévut par [Joomla! 3](https://github.com/joomla/joomla-cms/). Vous devrez donc veuiller à utiliser le compilateur `lessphp-1.7.0.3`.