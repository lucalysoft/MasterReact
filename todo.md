- caching
	* dependency unit tests
- validators
	* Refactor validators to add validateValue() for every validator, if possible. Check if value is an array.
	* FileValidator: depends on CUploadedFile
	* CaptchaValidator: depends on CaptchaAction
	* DateValidator: should we use CDateTimeParser, or simply use strtotime()?
	* CompareValidator::clientValidateAttribute(): depends on CHtml::activeId()

memo
	* Minimal PHP version required: 5.3.7 (http://www.php.net/manual/en/function.crypt.php)
---

- base
	* module
	  - Module should be able to define its own configuration including routes. Application should be able to overwrite it.
	* application
- built-in console commands
	+ api doc builder
		* support for markdown syntax
		* support for [[name]]
		* consider to be released as a separate tool for user app docs
- i18n
	* consider using PHP built-in support and data
	* formatting: number and date
	* parsing??
	* make dates/date patterns uniform application-wide including JUI, formats etc.
- helpers
	* image
	* string
	* file
- web: TBD
	* get/setFlash() should be moved to session component
	* support optional parameter in URL patterns
	* Response object.
	* ErrorAction
- gii
    * move generation API out of gii, provide yiic commands to use it. Use same templates for gii/yiic.
	* i18n variant of templates
	* allow to generate module-specific CRUD
- assets
    * ability to manage scripts order (store these in a vector?)
	* http://ryanbigg.com/guides/asset_pipeline.html, http://guides.rubyonrails.org/asset_pipeline.html, use content hash instead of mtime + directory hash.
- Requirement checker
- Optional configurable input filtering in request
- widgets
    * if we're going to supply default ones, these should generate really unique IDs. This will solve a lot of AJAX-nesting problems.
- Make sure type hinting is used when components are passed to methods
- Decouple controller from application (by passing web application instance to controller and if not passed, using Yii::app())?
