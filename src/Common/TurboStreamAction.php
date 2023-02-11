<?php

declare(strict_types=1);

namespace SimpleWebApps\Common;

enum TurboStreamAction: string
{
  case Append = 'append';
  case Prepend = 'prepend';
  case Replace = 'replace';
  case Update = 'update';
  case Remove = 'remove';
  case Before = 'before';
  case After = 'after';
}
