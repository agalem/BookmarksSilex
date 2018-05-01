<?php
/**
 * Created by PhpStorm.
 * User: agalempaszek
 * Date: 29.04.2018
 * Time: 19:59
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TagType extends AbstractType {

	public function buildForm( FormBuilderInterface $builder, array $options ) {
		$builder->add(
			'name',
			TextType::class,
			[
				'label' => 'label.name',
				'required' => true,
				'attr' => [
					'max_length' => 128,
				],
			]
		);
	}

	public function getBlockPrefix() {
		return 'tag_type';
	}

}