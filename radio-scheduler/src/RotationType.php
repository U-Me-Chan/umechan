<?php

namespace Ridouchire\RadioScheduler;

enum RotationType
{
    case Random;
    case BestEstimate;
    case NewOrLongStangind;
    case AverageOrAboveEstimateAndLongStanding;
}
