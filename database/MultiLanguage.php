<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;


trait MultiLanguage
{

    public function bootMultiLanguage()
    {
        if ($this->tableExists('languages')) {
            if ($this->columnExists('language_columns')) {

            } else {
                throw new LanguageException('You need to add language_columns to your table');
            }
        } else {
            throw new LanguageException('You need to install language middleware for use multilanguage module');
        }
    }

}