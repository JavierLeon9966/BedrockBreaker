<?php

namespace JavierLeon9966\BedrockBreaker;

use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;

class BedrockConfig{
	use MarshalTrait;

	/** @var positive-int */
	#[Field]
	public int $maxExplodeCount;
	#[Field]
	public float $blastResistance;
}