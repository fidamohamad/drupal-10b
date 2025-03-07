<?php

namespace Drupal\commerce_cart_skip\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds the form to delete an Example.
 */

class CommerceCartSkipRuleDeleteForm extends EntityConfirmFormBase
{

    /**
     * {@inheritdoc}
     */
    public function getQuestion()
    {
        return $this->t('Are you sure you want to delete %name?', array('%name' => $this->entity->label()));
    }

    /**
     * {@inheritdoc}
     */
    public function getCancelUrl()
    {
        return new Url('entity.commerce_cart_skip_rule.collection');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmText()
    {
        return $this->t('Delete');
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->entity->delete();
        $this->messenger()->addMessage($this->t('Entity %label has been deleted.', array('%label' => $this->entity->label())));

        $form_state->setRedirectUrl($this->getCancelUrl());
    }
}
