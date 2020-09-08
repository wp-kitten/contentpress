<?php

namespace App\Helpers\Syndication;

interface ISyndication
{
    function getBaseTags();

    function getTitles( $maxLimit = 0 );

    function getEntries( $maxLimit = 0 );
}
