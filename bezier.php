<?php 
class Bezier  {
	
 private $sampleValues;
 private $mX1;
 private $mX2;
 private $mY1;
 private $mY2;
 
 private $NEWTON_ITERATIONS = 4;
 private $NEWTON_MIN_SLOPE = 0.001;
 private $SUBDIVISION_PRECISION = 0.0000001;
 private $SUBDIVISION_MAX_ITERATIONS = 10;

 private $kSplineTableSize = 11;
 private $kSampleStepSize;
 	
 function __construct($mX1, $mY1, $mX2, $mY2) {

	  if (!(0 <= $mX1 && $mX1 <= 1 && 0 <= $mX2 && $mX2 <= 1)) 
		throw new Error('bezier x values must be in [0, 1] range');
	  
	  $this->kSampleStepSize = 1.0 / ($this->kSplineTableSize - 1.0);
	  
	  $this->sampleValues = array();
	  
	  $this->mX1 = $mX1; $this->mY1 = $mY1; $this->mX2 = $mX2; $this->mY2 = $mY2;
		
	  // Precompute samples table
	  $this->sampleValues = array();
	  for ($i = 0; $i < $this->kSplineTableSize; ++$i)
		$this->sampleValues[$i] = $this->calcBezier($i * $this->kSampleStepSize, $mX1, $mX2);

  }
  
 private function getTForX ($aX) {
	  
    $intervalStart = 0.0;
    $currentSample = 1;
    $lastSample = $this->kSplineTableSize - 1;

    for (; $currentSample !== $lastSample && $this->sampleValues[$currentSample] <= $aX; ++$currentSample) 
    	$intervalStart += $this->kSampleStepSize;
  
    --$currentSample;

    // Interpolate to provide an initial guess for t
    $dist = ($aX - $this->sampleValues[$currentSample]) / ($this->sampleValues[$currentSample + 1] - $this->sampleValues[$currentSample]);
    $guessForT = $intervalStart + $dist * $this->kSampleStepSize;

    $initialSlope = $this->getSlope($guessForT, $this->mX1, $this->mX2);
	
    if($initialSlope >= $this->NEWTON_MIN_SLOPE) 
      return $this->newtonRaphsonIterate($aX, $guessForT, $this->mX1, $this->mX2);
    
	else if($initialSlope === 0.0)
      return $guessForT;
	  
    else 
      return $this->binarySubdivide($aX, $intervalStart, $intervalStart + $this->kSampleStepSize, $this->mX1, $this->mX2);
    
  }

  public function bezierEasing ($x) {
	  
		if($x === 0) 
			return 0;
		
		if($x === 1) 
			return 1;
		
		if($this->mX1 === $this->mY1 && $this->mX2 === $this->mY2) //linear easing
	 		return $x;
		
		return $this->calcBezier($this->getTForX($x), $this->mY1, $this->mY2);
  }
  
 private function A ($aA1, $aA2) { return 1.0 - 3.0 * $aA2 + 3.0 * $aA1; }
 private function B ($aA1, $aA2) { return 3.0 * $aA2 - 6.0 * $aA1; }
 private function C ($aA1)       { return 3.0 * $aA1; }

 // Returns x(t) given t, x1, and x2, or y(t) given t, y1, and y2.
 private function calcBezier ($aT, $aA1, $aA2) { return (($this->A($aA1, $aA2) * $aT + $this->B($aA1, $aA2)) * $aT + $this->C($aA1)) * $aT; }

 // Returns dx/dt given t, x1, and x2, or dy/dt given t, y1, and y2.
 private function getSlope ($aT, $aA1, $aA2) { return 3.0 * $this->A($aA1, $aA2) * $aT * $aT + 2.0 * $this->B($aA1, $aA2) * $aT + $this->C($aA1); }

 private function binarySubdivide ($aX, $aA, $aB, $mX1, $mX2) {
	 
	  $currentX = $currentT = $i = 0;
	 
	  do {
		$currentT = $aA + ($aB - $aA) / 2.0;
		$currentX = $this->calcBezier($currentT, $mX1, $mX2) - $aX;
		if ($currentX > 0.0) {
		  $aB = $currentT;
		} else {
		  $aA = $currentT;
		}
	  } while (abs($currentX) > $this->SUBDIVISION_PRECISION && ++$i < $this->SUBDIVISION_MAX_ITERATIONS);
	 
	  return $currentT;
 }

 private function newtonRaphsonIterate ($aX, $aGuessT, $mX1, $mX2) {
	 
	 for($i = 0; $i < $this->NEWTON_ITERATIONS; ++$i) {
		 
	   $currentSlope = $this->getSlope($aGuessT, $mX1, $mX2);
	   
	   if ($currentSlope === 0.0) 
		 return $aGuessT;
	 
	   $currentX = $this->calcBezier($aGuessT, $mX1, $mX2) - $aX;
	   $aGuessT -= $currentX / $currentSlope;
	 }
	 
	 return $aGuessT;
 } 
};

?>