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
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BookmarkType extends AbstractType {

	public function buildForm( FormBuilderInterface $builder, array $options ) {
		$builder->add(
			'title',
			TextType::class,
			[
				'label' => 'label.title',
				'required' => true,
				'attr' => [
					'max_length' => 128,
				],
				'constraints' => [
					new Assert\NotBlank(
						[
							'groups' => ['bookmark-default']
						]
					),
					new Assert\Length(
						[
							'groups' => ['bookmark-default'],
							'min' => 2,
							'max' => 128,
						]
					),
				],
			]
		);
		$builder->add(
			'url',
			UrlType::class,
			[
				'label' => 'label.url',
				'required' => true,
				'attr' => [
					'max_length' => 128,
				],
			]
		);
		$builder->add(
			'is_public',
			ChoiceType::class,
			[
				'label' => 'label.is_public',
				'choices'  => [
					'label.no' => 0,
					'label.yes' => 1,
				],
				'required' => true,
			]
		);
	}

	public function configureOptions( OptionsResolver $resolver ) {
		$resolver->setDefaults(
			[
				'validation_groups' => 'bookmark-default',
			]
		);
	}

	public function getBlockPrefix()
	{
		return 'bookmark_type';
	}

}