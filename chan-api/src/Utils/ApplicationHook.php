<?php

namespace PK\Utils;

enum ApplicationHook
{
    case before_run;
    case after_run;
    case before_send;
    case after_send;
}
