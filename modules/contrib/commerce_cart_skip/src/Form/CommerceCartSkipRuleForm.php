<?php

namespace Drupal\commerce_cart_skip\Form;

use Drupal\commerce_order\Entity\OrderItemType;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_product\Entity\ProductType;
use Drupal\commerce_product\Entity\ProductVariationType;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the CommerceCartSkipRule add and edit forms.
 */
class CommerceCartSkipRuleForm extends EntityForm
{

    /**
     * Constructs an CommerceCartSkipRuleForm object.
     *
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
     *   The entityTypeManager.
     */
    public function __construct(EntityTypeManagerInterface $entityTypeManager)
    {
        $this->entityTypeManager = $entityTypeManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function form(array $form, FormStateInterface $form_state)
    {
        $form = parent::form($form, $form_state);

        $entity = $this->entity;

        $form['label'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Label'),
            '#maxlength' => 255,
            '#default_value' => $entity->label(),
            '#required' => TRUE,
        ];

        $form['id'] = [
            '#type' => 'machine_name',
            '#default_value' => $entity->id(),
            '#machine_name' => [
                'exists' => [$this, 'exist'],
            ],
            '#disabled' => !$entity->isNew(),
        ];

        $form['enabled'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Enabled'),
            '#default_value' => $entity->get('enabled') ?? 1,
        ];

        $form['allow_cancel'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Allow user to cancel purchase from add to cart form'),
            '#default_value' => $entity->get('allow_cancel') ?? 0,
        ];

        $form['conditions'] = [
            '#type' => 'fieldset',
            '#title' => 'Conditions'
        ];

        $variationTypes = ['' => '-- none --'];
        $loaded = ProductVariationType::loadMultiple();
        foreach ($loaded as $type) {
            $variationTypes[$type->id()] = $type->label();
        }
        $form['conditions']['commerce_product_variation_type'] = [
            '#type' => 'select',
            '#title' => $this->t('Product variation type'),
            '#options' => $variationTypes,
            '#default_value' => $entity->get('conditions')['commerce_product_variation_type'] ?? null
        ];
        $productTypes = ['' => '-- none --'];
        $loaded = ProductType::loadMultiple();
        foreach ($loaded as $type) {
            $productTypes[$type->id()] = $type->label();
        }
        $form['conditions']['commerce_product_type'] = [
            '#type' => 'select',
            '#title' => $this->t('Product type'),
            '#options' => $productTypes,
            '#default_value' => $entity->get('conditions')['commerce_product_type'] ?? null
        ];

        $form['conditions']['price'] = [
            '#type' => 'commerce_price',
            '#title' => $this->t('Variation must equal this price?'),
            '#default_value' => $entity->get('conditions')['price'] ?? null
        ];

        $form['conditions']['authenticated'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('User must be authenticated?'),
            '#default_value' => $entity->get('conditions')['authenticated'] ?? null
        ];

        $form['ui'] = [
            '#type' => 'fieldset',
            '#title' => 'UI and text fields'
        ];

        foreach ($this->getTextFields() as $field => $data) {
            $form['ui'][$field] = [
                '#type' => 'textfield',
                '#title' => $this->t($field),
                '#default_value' => $entity->get('ui')[$field] ?? $data['default']
            ];
        }

        $form['ui']['success_result'] = [
            '#type' => 'select',
            '#title' => $this->t('Success result'),
            '#options' => [
                'message' => 'Message',
                'redirect' => 'Redirect',
            ],
            '#required' => true,
            '#default_value' => $entity->get('ui')['success_result'] ?? 'message'
        ];

        $form['order_values'] = [
            '#type' => 'fieldset',
            '#title' => 'Order and order item values to create'
        ];

        $orderTypes = [];
        $loaded = OrderType::loadMultiple();
        foreach ($loaded as $type) {
            $orderTypes[$type->id()] = $type->label();
        }
        $form['order_values']['order_type'] = [
            '#type' => 'select',
            '#title' => $this->t('Order type'),
            '#options' => $orderTypes,
            '#required' => true,
            '#default_value' => $entity->get('order_values')['order_type'] ?? null
        ];

        $orderItemTypes = [];
        $loaded = OrderItemType::loadMultiple();
        foreach ($loaded as $type) {
            $orderItemTypes[$type->id()] = $type->label();
        }
        $form['order_values']['order_item_type'] = [
            '#type' => 'select',
            '#title' => $this->t('Order item type'),
            '#options' => $orderItemTypes,
            '#required' => true,
            '#default_value' => $entity->get('order_values')['order_item_type'] ?? null
        ];

        $form['order_values']['price'] = [
            '#type' => 'commerce_price',
            '#title' => $this->t('Price'),
            '#default_value' => $entity->get('order_values')['price'] ?? [
                'number' => 0.00,
                'currency_code' => null
            ]
        ];

        $form['order_values']['quantity'] = [
            '#type' => 'number',
            '#title' => $this->t('Quantity'),
            '#default_value' => $entity->get('order_values')['quantity'] ?? 1
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state)
    {
        $rule = $this->entity;

        $rule->set('enabled', $form_state->getValue('enabled'));

        $rule->set('allow_cancel', $form_state->getValue('allow_cancel'));

        $rule->set('conditions', [
            'commerce_product_variation_type' => $form_state->getValue('commerce_product_variation_type'),
            'commerce_product_type' => $form_state->getValue('commerce_product_type'),
            'price' => $form_state->getValue('price'),
            'authenticated' => $form_state->getValue('authenticated'),
        ]);

        $rule->set('order_values', [
            'order_type' => $form_state->getValue('order_type'),
            'order_item_type' => $form_state->getValue('order_item_type'),
            'price' => $form_state->getValue('price'),
            'quantity' => $form_state->getValue('quantity'),
        ]);

        $ui = [
            'success_result' => $form_state->getValue('success_result')
        ];
        foreach ($this->getTextFields() as $field => $data) {
            $ui[$field] = $form_state->getValue($field);
        }
        $rule->set('ui', $ui);

        $status = $rule->save();

        if ($status === SAVED_NEW) {
            $this->messenger()->addMessage($this->t('The %label commerce cart skip rule created.', [
                '%label' => $rule->label(),
            ]));
        } else {
            $this->messenger()->addMessage($this->t('The %label commerce cart skip rule updated.', [
                '%label' => $rule->label(),
            ]));
        }

        $form_state->setRedirect('entity.commerce_cart_skip_rule.collection');
    }

    /**
     * Helper function to check whether a configuration entity exists.
     */
    public function exist($id)
    {
        $entity = $this->entityTypeManager->getStorage('commerce_cart_skip_rule')->getQuery()
            ->condition('id', $id)
            ->accessCheck(TRUE)
            ->execute();
        return (bool) $entity;
    }

    public function getTextFields()
    {
        return [
            'message_bought' => [
                'name' => 'Success message',
                'default' => 'You have bought this product.'
            ],
            'button_buy' => [
                'name' => 'Button to buy',
                'default' => 'Buy'
            ],
            'button_bought' => [
                'name' => 'Text when disabled because already purchased',
                'default' => 'Already bought'
            ],
            'terms_text' => [
                'name' => 'Text for above button, e.g. terms and conditions',
                'default' => null
            ],
            'terms_link' => [
                'name' => 'Link for text',
                'default' => null
            ],
            'message_error' => [
                'name' => 'Error message',
                'default' => 'Error, could not purchase.'
            ],
            'cancel_button' => [
                'name' => 'Button text to cancel',
                'default' => 'Cancel'
            ],
            'cancel_success' => [
                'name' => 'Message for successfully cancelled',
                'default' => 'Purchase cancelled.'
            ],
            'bought_link_text' => [
                'name' => 'View order',
                'default' => 'View my purchase'
            ],
        ];
    }
}
