const nodemailer = require('nodemailer');

// Create email transporter
const transporter = nodemailer.createTransport({
  host: process.env.EMAIL_HOST || 'smtp.gmail.com',
  port: parseInt(process.env.EMAIL_PORT) || 587,
  secure: false,
  auth: {
    user: process.env.EMAIL_USER,
    pass: process.env.EMAIL_PASSWORD
  }
});

// Email templates
const emailTemplates = {
  addBalanceApproved: (username, amount) => ({
    subject: 'Add Balance Request Approved',
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #00a8ff;">Balance Addition Approved</h2>
        <p>Hello ${username},</p>
        <p>Your add balance request has been approved.</p>
        <p><strong>Amount:</strong> $${amount}</p>
        <p>Your account balance has been updated.</p>
        <p>Thank you for using GlobalSwiftPay2!</p>
      </div>
    `
  }),

  addBalanceDeclined: (username) => ({
    subject: 'Add Balance Request Declined',
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #00a8ff;">Balance Addition Declined</h2>
        <p>Hello ${username},</p>
        <p>Unfortunately, your add balance request has been declined.</p>
        <p>Please contact support if you have any questions.</p>
        <p>Thank you for using GlobalSwiftPay2!</p>
      </div>
    `
  }),

  withdrawalApproved: (username, amount) => ({
    subject: 'Withdrawal Request Approved',
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #00a8ff;">Withdrawal Approved</h2>
        <p>Hello ${username},</p>
        <p>Your withdrawal request has been approved.</p>
        <p><strong>Amount:</strong> $${amount}</p>
        <p>The funds will be processed shortly.</p>
        <p>Thank you for using GlobalSwiftPay2!</p>
      </div>
    `
  }),

  withdrawalDeclined: (username, amount) => ({
    subject: 'Withdrawal Request Declined',
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #00a8ff;">Withdrawal Declined</h2>
        <p>Hello ${username},</p>
        <p>Unfortunately, your withdrawal request for $${amount} has been declined.</p>
        <p>Please contact support if you have any questions.</p>
        <p>Thank you for using GlobalSwiftPay2!</p>
      </div>
    `
  }),

  transferApproved: (username, amount, recipientUsername) => ({
    subject: 'Transfer Request Approved',
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #00a8ff;">Transfer Approved</h2>
        <p>Hello ${username},</p>
        <p>Your transfer request has been approved.</p>
        <p><strong>Amount:</strong> $${amount}</p>
        <p><strong>Recipient:</strong> ${recipientUsername}</p>
        <p>The transfer has been completed successfully.</p>
        <p>Thank you for using GlobalSwiftPay2!</p>
      </div>
    `
  }),

  transferDeclined: (username, amount) => ({
    subject: 'Transfer Request Declined',
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #00a8ff;">Transfer Declined</h2>
        <p>Hello ${username},</p>
        <p>Unfortunately, your transfer request for $${amount} has been declined.</p>
        <p>Please contact support if you have any questions.</p>
        <p>Thank you for using GlobalSwiftPay2!</p>
      </div>
    `
  })
};

async function sendEmail(to, templateName, ...templateArgs) {
  try {
    const template = emailTemplates[templateName](...templateArgs);
    
    const info = await transporter.sendMail({
      from: process.env.EMAIL_FROM || 'GlobalSwiftPay2 <noreply@globalswiftpay2.com>',
      to: to,
      subject: template.subject,
      html: template.html
    });

    console.log('Email sent:', info.messageId);
    return true;
  } catch (error) {
    console.error('Error sending email:', error);
    return false;
  }
}

module.exports = {
  sendEmail
};
