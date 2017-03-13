<?php namespace PhpFanatic\clarifAI;
/**
 * Image client for the clarifAI API.
 *
 * @author   Nick White <git@phpfanatic.com>
 * @link     https://github.com/PHPfanatic/clarifai
 * @version  1.0.0
 */

use PhpFanatic\clarifAI\Api\AbstractBaseApi;
use PhpFanatic\clarifAI\Response\Response;

class ImageClient extends AbstractBaseApi 
{	
	public $data = array();
	public $image;
	public $search;
	public $concept;
	public $paginate;
	
	
	private $models = [
			'General'=>'aaa03c23b3724a16a56b629203edc62c',
			'Adult'=>'e9576d86d2004ed1a38ba0cf39ecb4b1',
			'Weddings'=>'c386b7a870114f4a87477c0824499348',
			'Travel'=>'eee28c313d69466f836ab83287a54ed9',
			'Food'=>'bd367be194cf45149e75f01d59f77ba7',
			'Color'=>'eeed0b6733a644cea07cf4c60f87ebb7',
			'Apparel'=>'e0be3b9d6a454f0493ac3a30784001ff',
			'Celebrity'=>'e466caa0619f444ab97497640cefc4dc',
			'Face'=>'a403429f2ddf4b49b307e318f00e528b'
	];
	
	public function __construct($clientid, $clientsecret) {
		parent::__construct($clientid, $clientsecret);
	}
	
	/**
	 * Create a model.
	 * @param string $model
	 * @param string $model_id
	 * @return string Json response from ClarifAI.
	 */
	public function ModelAdd($model, $model_id) {
		$data = array('model'=>array('name'=>$model, 'id'=>$model_id));
		$json = json_encode($data);
		
		if(!$this->IsTokenValid()) {
			if($this->GenerateToken() === false) {
				throw new \ErrorException('Token generation failed.');
			}
		}
		
		$service='models';
		$result = $this->SendPost($json, $service);
		
		return (Response::GetJson($result));
	}
	
	/**
	 * Update a model with a concept
	 * @param string $id Model ID to be updated.
	 * @param array $concepts Array of concepts to be applied.
	 * @param string $action action to apply merge|remove.
	 * @throws \ErrorException
	 * @return string Json response from ClarifAI.
	 */
	public function ModelUpdate($id, $concept, $action='merge') {		
		$build = array('id'=>$id, 'output_info'=>array('data'=>array('concepts'=>$concept)));
		$data['models'][] = $build;
		$data['action']=$action;
		
		if(!$this->IsTokenValid()) {
			if($this->GenerateToken() === false) {
				throw new \ErrorException('Token generation failed.');
			}
		}
		
		$service = 'models';
		$json = json_encode($data);
				
		$result = $this->SendPatch($json, $service);
		
		return (Response::GetJson($result));
	}
	
	/**
	 * Retrieve a list of models
	 * @param string $id Model ID
	 * @throws \ErrorException
	 * @return string Json response from ClarifAI.
	 */
	public function ModelGet($id=null) {
		if(!$this->IsTokenValid()) {
			if($this->GenerateToken() === false) {
				throw new \ErrorException('Token generation failed.');
			}
		}
		
		$service = 'models';
		
		if(is_array($this->paginate)) {
			$service = $service . '?page='.$this->paginate['page'].'&per_page='.$this->paginate['count'];
		}
		
		$id = array($id);
		$result = $this->SendGet($id, $service);
		
		return (Response::GetJson($result));
	}
	
	/**
	 * Train a model
	 * @param string $id Model ID.
	 * @throws \ErrorException
	 * @return string Json response from ClarifAI.
	 */
	public function ModelTrain($id) {
		if(!$this->IsTokenValid()) {
			if($this->GenerateToken() === false) {
				throw new \ErrorException('Token generation failed.');
			}
		}
		
		$service = 'models/'.$id.'/versions';
		$result = $this->SendPost(null, $service);
		
		return (Response::GetJson($result));
	}
		
	/**
	 * Predict image content based on model passed in.  You can pass in the name of existing ClarifAI models which will
	 * be automatically converted to their hash strings or you can pass in your own hash string from a custom model
	 * that you have created.
	 * @param string $model 
	 * @throws \LogicException
	 * @throws \ErrorException
	 * @return string Json response from ClarifAI.
	 */
	public function Predict($model='General') {
		if(!isset($this->image) || !is_array($this->image)) {
			throw new \LogicException('You must add at least one image via AddImage().');
		}
				
		if(!$this->IsTokenValid()) {
			if($this->GenerateToken() === false) {
				throw new \ErrorException('Token generation failed.');
			}
		}
		
		// Custom model handler.
		if(!array_key_exists($model, $this->models)) {
			$service = 'models/' . $model . '/outputs';
		}
		else {
			$service = 'models/' . $this->models[$model] . '/outputs';
		}
		
		$json = json_encode($this->image);
		$result = $this->SendPost($json, $service);
		
		return (Response::GetJson($result));
	}
	
	/**
	 * Add an image(s) to be indexed.
	 * @throws \LogicException
	 * @throws \ErrorException
	 * @return string Json response from ClarifAI.
	 */
	public function InputsAdd() {
		if(!isset($this->image) || !is_array($this->image)) {
			throw new \LogicException('You must add at least one image via AddImage().');
		}
		
		if(!$this->IsTokenValid()) {
			if($this->GenerateToken() === false) {
				throw new \ErrorException('Token generation failed.');
			}
		}
		
		$service = 'inputs';
		$json = json_encode($this->image);
		$result = $this->SendPost($json, $service);
		
		return (Response::GetJson($result));
	}
	
	/**
	 * Update an input by adding or deleting concepts for it.
	 * The default action is to merge concepts to the given input id(s).
	 * To delete a concept(s), pass 'remove' as the action variable.
	 * 
	 * @param string $action Action to be taken on the set concepts.
	 * @return string Json response from ClarifAI.  
	 */
	public function InputsUpdate($action='merge') {
		if(!isset($this->concept) || !is_array($this->concept)) {
			throw new \LogicException('You must add at least one concept via AddConcept()');
		}
		
		if(!$this->IsTokenValid()) {
			if($this->GenerateToken() === false) {
				throw new \ErrorException('Token generation failed.');
			}
		}
		
		$service = 'inputs';
		$this->concept['action']=$action;
		$json = json_encode($this->concept);
		
		$result = $this->SendPatch($json, $service);
		
		return (Response::GetJson($result));
	}

	/**
	 * Delete an image by its ID.  You can pass in either a single id as $id={ID1} or
	 * you can submit an array of id's $id=array({ID1},{ID2},{ID3}).
	 * @param mixed $id ID of image to delete
	 * @throws \ErrorException
	 * @return string Json response from ClarifAI.
	 */
	public function InputsDelete($id) {
		if(!$this->IsTokenValid()) {
			if($this->GenerateToken() === false) {
				throw new \ErrorException('Token generation failed.');
			}
		}
		
		if(is_array($id)) {
			$data['ids'] = $id;
			$json = json_encode($data);
			$service = 'inputs';
		}
		else {
			$service = 'inputs/'.$id;
			$json = '';
		}
		
		$result = $this->SendDelete($json, $service);
		
		return (Response::GetJson($result));
	}
	
	/**
	 * Return inputs that you have indexed, you may pass an ID to return a specific input.
	 * @param string $data
	 * @throws \ErrorException
	 * @return string Json response from ClarifAI.
	 */
	public function InputsGet($id=null) {
		if(!$this->IsTokenValid()) {
			if($this->GenerateToken() === false) {
				throw new \ErrorException('Token generation failed.');
			}
		}
		
		$service = 'inputs';
		
		if(is_array($this->paginate)) {
			$service = $service . '?page='.$this->paginate['page'].'&per_page='.$this->paginate['count'];
		}
		
		$data = array($id);
		$result = $this->SendGet($data, $service);
		
		return (Response::GetJson($result));
	}
	
	/**
	 * Get the status of your bulk inputs
	 * @throws \ErrorException
	 * @return string Json response from ClarifAI.
	 */
	public function InputsGetStatus() {
		if(!$this->IsTokenValid()) {
			if($this->GenerateToken() === false) {
				throw new \ErrorException('Token generation failed.');
			}
		}
		
		$service = 'inputs';
		$data = array('status');
		$result = $this->SendGet($data, $service);
		
		return (Response::GetJson($result));
	}
	
	/**
	 * Search your indexed images, you may search by concept, user concept, metadata or url.
	 * the $term variable should only be an array when searching metadata.
	 * ClarifAI... this search array structure hurts my head.
	 * @throws InvalidArgumentException
	 * @throws ErrorException
	 * @param mixed $term
	 * @param string $by
	 * @param bool $exists
	 * @return string Json response from ClarifAI. 
	 */
	public function Search($term, $by='concept', $exists=true) {
		$search_by = array(
				'concept'		=> array('data_type'=>'concepts', 'direction'=>'output', 'content'=>array(array('name'=>$term, 'value'=>$exists))),
				'user_concept'	=> array('data_type'=>'concepts', 'direction'=>'input',  'content'=>array(array('name'=>$term, 'value'=>$exists))),
				'meta'			=> array('data_type'=>'metadata', 'direction'=>'input',  'content'=>array($term[0]=>$term[1])),
				'url'			=> array('data_type'=>'image', 	  'direction'=>'input',  'content'=>array('url'=>$term)),
				'image'			=> array('data_type'=>'image',	  'direction'=>'output', 'content'=>array('url'=>$term))
		);
		
		// Light validation
		if(!array_key_exists($by, $search_by)) {
			throw new \InvalidArgumentException('Invalid \'search by\' parameter.');
		}
		
		if($by == 'meta' && !is_array($term)) {
			throw new \InvalidArgumentException('Metadata search requires your search term to be an array of [0]=key, [1]=value.');
		}
		
		if($by != 'meta' && !is_string($term)) {
			throw new \InvalidArgumentException('Search term should be a string.');
		}
		
		$this->search = array(
			'query'=>array(
				'ands'=>array(
					array(
						$search_by[$by]['direction']=>array(
							'data'=>array(
								$search_by[$by]['data_type']=>$search_by[$by]['content']
							)
						)
					)
				)
			)
		);
		
		// Dynamically adjust output/input for image search.
		if($by === 'image') {
			$this->search['query']['ands'][0]['output'] = array('input'=>$this->search['query']['ands'][0]['output']);
		}
		
		if(!$this->IsTokenValid()) {
			if($this->GenerateToken() === false) {
				throw new \ErrorException('Token generation failed.');
			}
		}
		
		$service = 'searches';
		$json = json_encode($this->search);
		$result = $this->SendPost($json, $service);
		
		return (Response::GetJson($result));
	}
	
	/**
	 * Prepare concepts for API call.  This stores the concept in $this->concept.  Calling AddConcept()
	 * multiple times will build and array of concepts to be posted.
	 * 
	 * @param string $id Input ID to modify for this concept.
	 * @param array $concepts Array of id=>value 
	 * @return null
	 */
	public function AddConcept($id, $concepts) {
		$data = array('id'=>$id, 'data'=>array('concepts'=>array()));
		$data['data']['concepts'] = $concepts;
		
		$this->concept['inputs'][] = $data;
		
		return null;
	}
		
	/**
	 * Prepare images for API call.  This stores the image and optional data in $this->image.  Calling AddImage()
	 * multiple times will build an array of images to be posted.  A maximum of 128 images per post is allowed.
	 * 
	 * @param string $image url to image or image in bytes.
	 * @param string $id identifier to use with image when calling "inputs".
	 * @param array $concept optional concept data, see documentation for structure.
	 * @param array $metadata optional metadata, see documentation for structure.
	 * @param array $crop optional image crop data, see documentaiton for structure.
	 * @return null
	 */
	public function AddImage($image, $id='', $concept=array(), $metadata=array(), $crop=array()) {
		//Base package format
		$data = array('data'=>array('image'=>array()));
	
		//If id passed in, typically for input commands.
		if($id != '') {
			$data['id']=$id;
		}
		
		//Is the image a url or bytes
		if(filter_var($image, FILTER_VALIDATE_URL) === FALSE) {
			$data['data']['image']['base64'] = base64_encode($image);
		} else {
			$data['data']['image']['url'] = $image;
		}
	
		if(count($concept)) {
			$data['data']['concepts'] = array($concept);
		}
	
		if(count($metadata)) {
			$data['data']['metadata'] = $metadata;
		}
	
		if(count($crop)) {
			$data['data']['image']['crop'] = $crop;
		}
		
		$this->image['inputs'][] = $data;
		
		return null;
	}
	
	/**
	 * Returns json with each image that is currently active within the ImageClient object.
	 * @return string
	 */
	public function ShowImage() {
		return json_encode($this->image);
	}
	
	/**
	 * Set pagination for the next call.
	 * @param int $page
	 * @param int $count
	 * @return null
	 */
	public function Paginate($page, $count) {
		$this->paginate = array('page'=>$page, 'count'=>$count);
		
		return null;
	}
	
	/**
	 * Move pagination forward one page.
	 * @throws \LogicException
	 * @param int $page number of pages to move forward.
	 * @return null
	 */
	public function PageForward($page=1) {
		if(!is_array($this->paginate)) {
			throw new \LogicException('You must initiate paginate first via Paginate()');	
		}
		
		$this->paginate['page'] = $this->paginate['page']+$page;
		
		return null;
	}
	
	/**
	 * Move pagination back one page.
	 * @throws \LogicException
	 * @return null
	 */
	public function PageBack($page=1) {
		if(!is_array($this->paginate)) {
			throw new \LogicException('You must initiate paginate first via Paginate()');
		}
		
		// Only adjust paginate page if we are not on page 1.
		if($this->paginate['page'] > 1) {
			$this->paginate['page'] = $this->paginate['page']-$page;
		}
				
		return null;
	}
		
	/**
	 * Clear/reset $this->image variable after adding an image via AddImage.
	 *
	 * @return null  
	 */
	public function ClearImages() {
		unset($this->image);
		return null;
	}
	
	/**
	 * Clear/reset $this->concept variable after adding a concept via AddConcept.
	 *
	 * @return null
	 */
	public function ClearConcepts() {
		unset($this->concept);
		return null;
	}
	
	/**
	 * Clear/rest $this->pagination.
	 * @return NULL
	 */
	public function ClearPagination() {
		unset($this->paginate);
		return null;
	}
}