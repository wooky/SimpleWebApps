<?php

declare(strict_types=1);

namespace SimpleWebApps\EventBus;

enum EventScope: string
{
  case SpecifiedTopic = 'specified_topic';
  case AllTopics = 'all_topics';
  case AllUsersOfSpecifiedTopic = 'all_users_of_specified_topic';
}
