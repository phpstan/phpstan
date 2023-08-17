# How to reproduce
1. Install all dependencies (`composer install`)
2. Ensure the PHPStan extension installer is enabled
3. Run `./vendor/bin/phpstan`
4. The following error should be shown:

```bash
 -- ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- 
     Error                                                                                                                                                                                          
 -- ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- 
     Internal error: Internal error: No closure found on line 166 in {project dir}/vendor/cmixin/business-day/src/Cmixin/BusinessDay/Holiday.php in file  
     {project dir}/packages/backend/src/Services/MyClass.php                                                                                              
     Run PHPStan with -v option and post the stack trace to:                                                                                                                                        
     https://github.com/phpstan/phpstan/issues/new?template=Bug_report.yaml                                                                                                                         
     Child process error (exit code 1):                                                                                                                                                             
 -- ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- 

```
